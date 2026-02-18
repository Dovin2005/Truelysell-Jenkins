<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ServicePromotion;
use Modules\GlobalSetting\app\Models\SubscriptionPackage;
use Illuminate\Support\Facades\Auth;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Carbon\Carbon;
use Modules\GlobalSetting\Entities\GlobalSetting;
use Modules\Service\app\Models\Service;

class PromotedServicePaymentController extends Controller
{

    public function showPromotedServicePage(Service $service)
    {
        $packages = SubscriptionPackage::where('promoted_service', true)->get();

        $paymentInfo = GlobalSetting::where('group_id', 13)
            ->whereIn('key', [
                'stripe_status',
                'paypal_status',
                'cod_status',
                'wallet_status',
                'mollie_status',
            ])
            ->pluck('value', 'key')
            ->toArray();

        $activePromotion = ServicePromotion::where('service_id', $service->id)
            ->where('payment_status', 'paid')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        $currencySymbol = getDefaultCurrencySymbol();

        return view('provider.promote-service', [
            'packages' => $packages,
            'paymentInfo' => $paymentInfo,
            'serviceId' => $service->id,
            'activePromotion' => $activePromotion,
            'currencySymbol' => $currencySymbol
        ]);
    }
    public function pay(Request $request)
    {
        $request->validate([
            'package_id' => 'required',
            'service_id' => 'required',
            'payment_method' => 'required|in:stripe,paypal',
        ]);

        $package = SubscriptionPackage::findOrFail($request->package_id);

        if ($request->payment_method === 'stripe') {
            return $this->payWithStripe($package, $request);
        }

        return $this->payWithPaypal($package, $request);
    }

    private function payWithStripe($package, $request)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $session = StripeSession::create([
            'payment_method_types' => ['card'],
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => $package->package_title,
                        ],
                        'unit_amount' => $package->price * 100,
                    ],
                    'quantity' => 1,
                ]
            ],
            'mode' => 'payment',
            'success_url' => route('provider.promote.stripe.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('provider.dashboard') . '?stripe=cancel',
        ]);

        ServicePromotion::create([
            'provider_id' => Auth::id(),
            'service_id' => $request->service_id,
            'package_id' => $package->id,
            'amount' => $package->price,
            'payment_gateway' => 'stripe',
            'payment_status' => 'pending',
            'transaction_id' => $session->id,
        ]);

        return response()->json(['url' => $session->url]);
    }

    private function payWithPaypal($package, $request)
    {
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();

        $order = $provider->createOrder([
            "intent" => "CAPTURE",
            "purchase_units" => [
                [
                    "amount" => [
                        "currency_code" => "USD",
                        "value" => $package->price
                    ]
                ]
            ],
            "application_context" => [
                "return_url" => route('provider.promote.paypal.success'),
                "cancel_url" => route('provider.promote.paypal.cancel'),
            ]
        ]);

        ServicePromotion::create([
            'provider_id' => Auth::id(),
            'service_id' => $request->service_id,
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

        return redirect()->route('provider.dashboard')->with('success', 'Service promoted successfully');
    }

    public function stripeSuccess(Request $request)
    {
        $request->validate([
            'session_id' => 'required',
        ]);

        Stripe::setApiKey(config('services.stripe.secret'));

        // Retrieve session from Stripe
        $session = StripeSession::retrieve($request->session_id);

        // 🔒 Security check
        if ($session->payment_status !== 'paid') {
            return redirect()
                ->route('provider.dashboard')
                ->with('error', 'Stripe payment not completed');
        }

        // Find promotion using transaction_id (session id)
        $promotion = ServicePromotion::where(
            'transaction_id',
            $session->id
        )->firstOrFail();

        // Activate promotion
        $promotion->update([
            'payment_status' => 'paid',
            'starts_at' => now(),
            'expires_at' => now()->addDays(
                SubscriptionPackage::find($promotion->package_id)->package_duration
            ),
        ]);

        return redirect()
            ->route('provider.dashboard')
            ->with('success', 'Service promoted successfully');
    }
}