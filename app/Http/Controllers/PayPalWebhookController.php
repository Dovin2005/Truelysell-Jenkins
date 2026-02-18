<?php

namespace App\Http\Controllers;

use App\Models\Bookings;
use App\Models\PackageTrx;
use App\Models\PayoutHistory;
use Illuminate\Http\Request;
use App\Models\ProviderRequestAmount;
use App\Services\PayPalSubscriptionService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\GlobalSetting\app\Models\SubscriptionPackage;
use Modules\GlobalSetting\Entities\GlobalSetting;

class PayPalWebhookController extends Controller
{
    private $clientId;
    private $secret;
    private $baseUrl;
    public function __construct()
    {
        $this->baseUrl = env('PAYPAL_MODE') === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';

        $this->clientId = env('PAYPAL_SANDBOX_CLIENT_ID');
        $this->secret = env('PAYPAL_SANDBOX_CLIENT_SECRET');
    }

    public function handleSubscriptionWebhook(Request $request)
    {
        $payload = $request->all();
        $eventType = $payload['event_type'] ?? null;
        $resource = $payload['resource'] ?? [];
        //log($payload);
        Log::info('PayPal Subscription Webhook Received', [
            'event_type' => $eventType,
            'resource' => $resource,
        ]);
        // Get billing agreement / subscription ID
        $subscriptionId = $resource['billing_agreement_id']
            ?? ($resource['supplementary_data']['related_ids']['billing_agreement_id'] ?? null)
            ?? ($resource['id'] ?? null);


        // Log every webhook for traceability
        Log::info('PayPal Subscription Webhook Received', [
            'event_type' => $eventType,
            'subscription_id' => $subscriptionId,
        ]);

        // If there is no billing_agreement_id then this is not related to subscriptions
        if (!$subscriptionId) {
            Log::info("PayPal Webhook Ignored: Event {$eventType} has no billing_agreement_id (not a subscription payment)");
            return response()->json(['status' => 'ignored'], 200);
        }

        //Find our local subscription transaction
        $transaction = PackageTrx::where('paypal_subscription_id', $subscriptionId)->latest()->first();

        if (!$transaction) {
            Log::warning("PayPal Webhook: No transaction found for subscription {$subscriptionId}");
            return response()->json(['status' => 'no_transaction'], 200);
        }

        $package = SubscriptionPackage::find($transaction->package_id);

        // Handle subscription-related event types
        switch ($eventType) {
            case 'PAYMENT.SALE.COMPLETED':
                $nextBillingTime = $resource['billing_info']['next_billing_time'] ?? null;
                $endDate = $nextBillingTime
                    ? Carbon::parse($nextBillingTime)
                    : match ($package->package_term) {
                        'day' => Carbon::parse($transaction->end_date)->addDays($package->package_duration),
                        'month' => Carbon::parse($transaction->end_date)->addMonths($package->package_duration),
                        'yearly' => Carbon::parse($transaction->end_date)->addYears($package->package_duration),
                        default => Carbon::parse($transaction->end_date)->addMonth(),
                    };

                $transaction->update([
                    'payment_status' => 2,
                    'status' => 1,
                    'end_date' => $endDate,
                    'description' => 'PayPal payment completed successfully (renewal or initial). webhook',
                ]);

                Log::info("PayPal: Payment completed for subscription {$subscriptionId}");
                //Cancel other active subscriptions for this provider
                PackageTrx::where('provider_id', $transaction->provider_id)
                    ->where('status', 1)
                    ->where('id', '!=', $transaction->id)
                    ->update([
                        'status' => 0,
                        'end_date' => now(),
                        'description' => 'New PayPal subscription activated, old one cancelled automatically. webhook',
                    ]);
                break;

            case 'PAYMENT.SALE.DENIED':
            case 'BILLING.SUBSCRIPTION.PAYMENT.FAILED':
            case 'BILLING.SUBSCRIPTION.CANCELLED':
            case 'BILLING.SUBSCRIPTION.SUSPENDED':
                $transaction->update([
                    'payment_status' => 1,
                    'status' => 0,
                    'description' => "PayPal subscription failed or cancelled via {$eventType}. webhook",
                ]);

                // Cancel in PayPal server
                $this->cancelPaypalSubscription($subscriptionId);

                // Deactivate all other active subscriptions (safety)
                PackageTrx::where('provider_id', $transaction->provider_id)
                    ->where('status', 1)
                    ->update([
                        'status' => 0,
                        'end_date' => now(),
                        'description' => "Deactivated due to PayPal webhook {$eventType}",
                    ]);

                Log::warning("PayPal: Subscription {$subscriptionId} cancelled in PayPal and locally.");
                break;

            case 'BILLING.SUBSCRIPTION.RE-ACTIVATED':
                $transaction->update([
                    'status' => 1,
                    'description' => 'PayPal subscription re-activated by user or system. webhook',
                ]);
                Log::info("PayPal: Subscription re-activated {$subscriptionId}");
                break;

            default:
                Log::info("PayPal Webhook Ignored: {$eventType}");
                break;
        }

        return response()->json(['status' => 'ok'], 200);
    }

    /**
     * Cancel subscription directly in PayPal
     */
    private function cancelPaypalSubscription(string $subscriptionId)
    {
        try {
            $accessToken = app(PayPalSubscriptionService::class)->getAccessToken();
            if (!$accessToken) {
                return;
            }
            $endpoint = $this->baseUrl . '/v1/billing/subscriptions/' . $subscriptionId . '/cancel';
            $response = Http::withToken($accessToken)
                ->post($endpoint, [
                    'reason' => 'Cancelled automatically by webhook after payment failure or manual cancellation.',
                ]);

            if ($response->successful()) {
                Log::info("PayPal: Successfully cancelled subscription {$subscriptionId} via API");
            } else {
                Log::error("PayPal Cancel API failed for {$subscriptionId}", [
                    'response' => $response->json(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error("PayPal cancel error: {$e->getMessage()}");
        }
    }
}
