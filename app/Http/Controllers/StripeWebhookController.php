<?php

namespace App\Http\Controllers;

use App\Models\PackageTrx;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Stripe\StripeClient;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function handleSubscriptionWebhook(Request $request)
    {
        // Get raw payload for signature verification
        $rawBody = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = env('STRIPE_WEBHOOK_SECRET');

        try {
            // Verify event authenticity
            $event = Webhook::constructEvent($rawBody, $sigHeader, $endpointSecret);
        } catch (\UnexpectedValueException $e) {
            Log::error('Stripe webhook JSON error: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::error('Stripe signature verification failed: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Event type
        $eventType = $event->type ?? null;
        Log::info('Verified Stripe Webhook Received', ['event' => $eventType]);

        // Event data object
        $object = $event->data->object ?? null;

        switch ($eventType) {

            case 'invoice.payment_succeeded':
                $subscriptionId =
                    $object->subscription ??
                    ($object->parent->subscription_details->subscription ?? null);

                $periodEnd = $object->lines->data[0]->period->end ?? null;

                Log::info('invoice.payment_succeeded', [
                    'invoice_id'   => $object->id,
                    'subscription' => $subscriptionId,
                    'amount_paid'  => $object->amount_paid ?? null,
                    'period_end'   => $periodEnd,
                ]);

                if ($subscriptionId) {
                    $transaction = PackageTrx::where('stripe_subscription_id', $subscriptionId)->latest()->first();
                    PackageTrx::where('stripe_subscription_id', $subscriptionId)->update([
                        'payment_status' => 2, // Paid
                        'status' => 1, // Active
                        'end_date' => $periodEnd ? Carbon::createFromTimestamp($periodEnd) : now(),
                        'description' => 'Payment succeeded - invoice ' . $object->id . ' webhook',
                        'updated_at' => now(),
                    ]);

                    if ($transaction) {
                        //Cancel other active subscriptions for this provider
                        PackageTrx::where('provider_id', $transaction->provider_id)
                            ->where('status', 1)
                            ->where('id', '!=', $transaction->id)
                            ->update([
                                'status' => 0,
                                'end_date' => now(),
                                'description' => 'New Stripe subscription activated, old one cancelled automatically. webhook',
                            ]);
                    }
                } else {
                    Log::warning('invoice.payment_succeeded missing subscription id');
                }
                break;

            // Payment failed (card declined / insufficient funds)
            case 'invoice.payment_failed':
                $subscriptionId =
                    $object->subscription ??
                    ($object->parent->subscription_details->subscription ?? null);

                Log::warning('invoice.payment_failed', [
                    'invoice_id' => $object->id,
                    'subscription' => $subscriptionId,
                ]);

                if ($subscriptionId) {
                    PackageTrx::where('stripe_subscription_id', $subscriptionId)->update([
                        'payment_status' => 1, // Unpaid
                        'status' => 0, // Inactive
                        'description' => 'Payment failed - invoice ' . $object->id,
                        'updated_at' => now(),
                    ]);

                    try {
                        // Retrieve subscription to check status (idempotency)
                        $stripe = new StripeClient(env('STRIPE_SECRET'));

                        $remoteSub = $stripe->subscriptions->retrieve($subscriptionId, []);

                        $remoteStatus = $remoteSub->status ?? null;
                        Log::info('Stripe subscription fetch', ['subscription' => $subscriptionId, 'status' => $remoteStatus]);

                        if ($remoteStatus !== 'canceled') {
                            $cancelResult = $stripe->subscriptions->cancel($subscriptionId, []); // immediate cancel
                            Log::info('Stripe subscription canceled', [
                                'subscription' => $subscriptionId,
                                'cancel_result' => is_object($cancelResult) ? (array) $cancelResult : $cancelResult,
                            ]);
                        } else {
                            Log::info('Stripe subscription already canceled', ['subscription' => $subscriptionId]);
                        }
                    } catch (\Exception $e) {
                        // Log but don't throw — webhook should still respond 200 so Stripe doesn't retry infinitely.
                        Log::error('Error cancelling Stripe subscription: ' . $e->getMessage(), [
                            'subscription' => $subscriptionId,
                            'invoice' => $object->id ?? null,
                        ]);
                    }
                }
                break;

            // Subscription canceled manually or by Stripe
            case 'customer.subscription.deleted':
                $subscriptionId = $object->id ?? null;
                Log::info('customer.subscription.deleted', ['subscription' => $subscriptionId]);

                if ($subscriptionId) {
                    PackageTrx::where('stripe_subscription_id', $subscriptionId)->update([
                        'status' => 0,
                        'description' => 'Subscription canceled from Stripe webhook',
                        'updated_at' => now(),
                    ]);
                }
                break;

            /*------------------------------------------------
             |  Subscription updated (metadata, plan, etc)
             |  → Only log, don't extend end_date here.
             *-----------------------------------------------*/
            case 'customer.subscription.updated':
                Log::info('customer.subscription.updated event received - no DB changes applied');
                break;

            default:
                Log::info('Unhandled Stripe Event', ['type' => $eventType]);
                break;
        }

        return response()->json(['status' => 'success']);
    }
}
