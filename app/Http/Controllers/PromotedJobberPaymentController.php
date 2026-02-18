<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ServicePromotion;
use Modules\GlobalSetting\app\Models\SubscriptionPackage;
use Illuminate\Support\Facades\Auth;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Modules\GlobalSetting\Entities\GlobalSetting;

class PromotedJobberPaymentController extends Controller
{
    public function showPromotedJobberPage()
    {
        $packages = SubscriptionPackage::where('promoted_jobber', true)->get();

        $paymentInfo = GlobalSetting::where('group_id', 13)
            ->whereIn('key', ['stripe_status', 'paypal_status'])
            ->pluck('value', 'key')
            ->toArray();

        $activePromotion = ServicePromotion::where('provider_id', Auth::id())
            ->whereNull('service_id')
            ->where('payment_status', 'paid')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        $currencySymbol = getDefaultCurrencySymbol();

        return view('provider.promote-jobber', compact(
            'packages',
            'paymentInfo',
            'activePromotion',
            'currencySymbol'
        ));
    }

    public function pay(Request $request)
    {
        $request->validate([
            'package_id' => 'required',
            'payment_method' => 'required|in:stripe,paypal',
        ]);

        $package = SubscriptionPackage::findOrFail($request->package_id);

        return $request->payment_method === 'stripe'
            ? $this->payWithStripe($package)
            : $this->payWithPaypal($package);
    }

    private function payWithStripe($package)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $session = StripeSession::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => $package->package_title,
                    ],
                    'unit_amount' => $package->price * 100,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => route('provider.promote.jobber.stripe.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('provider.dashboard'),
        ]);

        ServicePromotion::create([
            'provider_id' => Auth::id(),
            'service_id' => null,
            'package_id' => $package->id,
            'amount' => $package->price,
            'payment_gateway' => 'stripe',
            'payment_status' => 'pending',
            'transaction_id' => $session->id,
        ]);

        return response()->json(['url' => $session->url]);
    }

    private function payWithPaypal($package)
    {
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();

        $order = $provider->createOrder([
            "intent" => "CAPTURE",
            "purchase_units" => [[
                "amount" => [
                    "currency_code" => "USD",
                    "value" => $package->price
                ]
            ]],
            "application_context" => [
                "return_url" => route('provider.promote.jobber.paypal.success'),
                "cancel_url" => route('provider.dashboard'),
            ]
        ]);

        ServicePromotion::create([
            'provider_id' => Auth::id(),
            'service_id' => null,
            'package_id' => $package->id,
            'amount' => $package->price,
            'payment_gateway' => 'paypal',
            'payment_status' => 'pending',
            'transaction_id' => $order['id'],
        ]);

        return response()->json([
            'url' => collect($order['links'])
                ->where('rel', 'approve')
                ->first()['href']
        ]);
    }

    public function paypalSuccess(Request $request)
    {
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();
        $provider->capturePaymentOrder($request->token);

        $promotion = ServicePromotion::where('transaction_id', $request->token)->firstOrFail();

        $promotion->update([
            'payment_status' => 'paid',
            'starts_at' => now(),
            'expires_at' => now()->addDays(
                SubscriptionPackage::find($promotion->package_id)->package_duration
            ),
        ]);

        return redirect()->route('provider.dashboard')
            ->with('success', 'Profile promoted successfully');
    }

    public function stripeSuccess(Request $request)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $session = StripeSession::retrieve($request->session_id);

        if ($session->payment_status !== 'paid') {
            return redirect()->route('provider.dashboard')
                ->with('error', 'Payment not completed');
        }

        $promotion = ServicePromotion::where('transaction_id', $session->id)->firstOrFail();

        $promotion->update([
            'payment_status' => 'paid',
            'starts_at' => now(),
            'expires_at' => now()->addDays(
                SubscriptionPackage::find($promotion->package_id)->package_duration
            ),
        ]);

        return redirect()->route('provider.dashboard')
            ->with('success', 'Profile promoted successfully');
    }
}
