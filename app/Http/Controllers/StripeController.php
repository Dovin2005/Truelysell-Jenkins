<?php

namespace App\Http\Controllers;

use Modules\GlobalSetting\Entities\GlobalSetting;
use Modules\GlobalSetting\app\Models\Currency;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Modules\Product\app\Models\Product;
use Illuminate\Http\Request;
use DB;
use App\Models\PackageTrx;
use Illuminate\Http\RedirectResponse;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Modules\Product\app\Models\Book;
use Modules\Leads\app\Models\Payments;
use Modules\Communication\app\Http\Controllers\EmailController;
use Modules\Communication\app\Models\Templates;
use App\Models\Bookings;
use Modules\Communication\app\Http\Controllers\NotificationController;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use App\Models\WalletHistory;
use Illuminate\Support\Facades\DB as FacadesDB;
use Illuminate\Support\Facades\Log;
use Modules\GlobalSetting\app\Models\SubscriptionPackage;
use App\Repositories\Contracts\StripeRepositoryInterface;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\StripeRequest;
use App\Http\Requests\StripepaymentRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Js;

class StripeController extends Controller
{
     protected $stripeRepository;

    public function __construct(StripeRepositoryInterface $stripeRepository)
    {
        $this->stripeRepository = $stripeRepository;
    }

    /**
     * @return View|Factory|Application
     */
    public function checkout(): View|Factory|Application
    {
        return view('bookingfail');
    }

    public function live_mobile(Request $request): JsonResponse
    {
        $response = $this->stripeRepository->live_mobile($request);
        return $response;
    }

    public function live_mobile_pay(StripeRequest $request): JsonResponse
    {
        $response = $this->stripeRepository->live_mobile_pay($request);
        return $response;
    }

    public function sub_payment_success(StripepaymentRequest $request): JsonResponse
    {
        $response = $this->stripeRepository->sub_payment_success($request);
        return $response;
    }

    /**
     * @return RedirectResponse
     * @throws ApiErrorException
     */
    public function test(Request $request): RedirectResponse
    {
        $response = $this->stripeRepository->test($request);
        return $response;
    }

    /**
     * @return RedirectResponse
     * @throws ApiErrorException
     */
    public function live(): RedirectResponse
    {
        $response = $this->stripeRepository->live();
        return $response;
    }

    /**
     * @return View|Factory|Application
     */
    public function paymentSuccess(Request $request): View|Factory|Application
    {
        $response = $this->stripeRepository->paymentSuccess($request);
        return $response;
    }

    public function stripepayment(Request $request): RedirectResponse
    {
        $response = $this->stripeRepository->stripepayment($request);
        return $response;
    }
    
    /**
     * @return View|Factory|Application
     */
    public function subscriptionpaymentsuccess(Request $request): View|Factory|Application
    {
        $response = $this->stripeRepository->subscriptionpaymentsuccess($request);
        return $response;
    }
    public function UserstripeSuccesspayment(Request $request): View|Factory|Application
    {
        Stripe::setApiKey(config('stripe.test.sk'));
        $sessionId = $request->get('session_id');
        Payments::where('transaction_id', $sessionId)->update(['status' => 2]);
        return view('user.userpaymentsuccess');
    }

    public function createCheckoutSession(Request $request)
    {
        $packageTrxId = $request->trx_id;
        $packageTrx = PackageTrx::find($packageTrxId);
        if (!$packageTrx) {
            return response()->json(['message' => 'Invalid package transaction.'], 422);
        }

        $package = SubscriptionPackage::find($packageTrx->package_id);
        $subscriptionType = $package->subscription_type ?? '';

        if ($subscriptionType == 'regular' && $package->stripe_price_id && $package->stripe_recurring) {
            if (!$package->stripe_price_id) {
                return response()->json(['message' => 'This package is not enabled for Stripe subscriptions.'], 422);
            }
            $stripeSecret = config('stripe.test.sk') ?? '';
            if (empty($stripeSecret)) {
                return response()->json([
                    'message' => 'Stripe is currently unavailable. Please choose another payment method.'
                ], 422);
            }

            Stripe::setApiKey($stripeSecret);

            $provider = Auth::user();
            $session = Session::create([
                'mode' => 'subscription',
                'customer_email' => $provider->email,
                'line_items' => [[
                    'price' => $package->stripe_price_id,
                    'quantity' => 1,
                ]],
                'success_url' => route('provider.stripe.subscription.success') . "?session_id={CHECKOUT_SESSION_ID}&subscription_type=" . $subscriptionType . "&is_recurring=true",
                'cancel_url' => route('provider.stripe.subscription.cancel') . '?session_id={CHECKOUT_SESSION_ID}',
            ]);
    
            //Create Pending Package Transaction
            PackageTrx::where('id', $packageTrxId)->update([
                'transaction_id' => $session->id,
                'payment_gateway' => 2, // 2 = Stripe
                'payment_type' => 2,
                'payment_status' => 1, // 1 = Unpaid
                'status' => 0, // inactive until payment confirmed
                'created_by' => $provider->id,
                'description' => 'Stripe subscription initiated.',
            ]);

            return response()->json(['url' => $session->url]);
        }
        else {
            return $this->createTopupCheckout($request, $packageTrx->package_id, $subscriptionType);
        }

    }

    public function createTopupCheckout(Request $request, $packageId, $subscriptionType)
    {
        $packageTrxId = $request->trx_id;
        $package = SubscriptionPackage::find($packageId);
        $stripeSecret = config('stripe.test.sk') ?? '';
        if (empty($stripeSecret)) {
            return response()->json([
                'message' => 'Stripe is currently unavailable. Please choose another payment method.'
            ], 422);
        }

        Stripe::setApiKey($stripeSecret);
        $provider = Auth::user();
        $currencyCode = getDefaultCurrencyCode();

        $session = \Stripe\Checkout\Session::create([
            'mode' => 'payment',  // <-- ONE-TIME PAYMENT
            'customer_creation' => 'always',
            'billing_address_collection' => 'required',
            'line_items' => [[
                'price_data' => [
                    'currency' => $currencyCode,
                    'product_data' => ['name' => $package->package_title],
                    'unit_amount' => $package->price * 100,
                ],
                'quantity' => 1,
            ]],
            'success_url' => route('provider.stripe.subscription.success') . "?session_id={CHECKOUT_SESSION_ID}&subscription_type=" . $subscriptionType . "&is_recurring=false",
            'cancel_url' => route('checkout') . '?session_id={CHECKOUT_SESSION_ID}',
        ]);

        PackageTrx::where('id', $packageTrxId)->update([
            'transaction_id' => $session->id,
            'payment_gateway' => 2, // 2 = Stripe
            'payment_type' => 2,
            'payment_status' => 1, // 1 = Unpaid
            'status' => 0, // inactive until payment confirmed
            'created_by' => $provider->id,
            'description' => 'Stripe one-time topup initiated.'
        ]);

        return response()->json(['url' => $session->url]);
    }

    public function subscriptionSuccess(Request $request)
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));
        $session = \Stripe\Checkout\Session::retrieve($request->session_id);

        $sessionId = $request->get('session_id');
        $subscriptionType = $request->get('subscription_type');
        $transaction = PackageTrx::where('transaction_id', $sessionId)->first();
        $isRecurring = $request->get('is_recurring');

        if ($transaction) {
            if ($subscriptionType == 'regular' && $isRecurring == 'true') {
                $subscription = \Stripe\Subscription::retrieve($session->subscription);
                $customer = \Stripe\Customer::retrieve($subscription->customer);

                $subscriptionPackageIds = SubscriptionPackage::where('subscription_type', 'regular')->pluck('id')->toArray();
                PackageTrx::where('provider_id', $transaction['provider_id'])
                    ->whereIn('package_id', $subscriptionPackageIds)
                    ->update(['status' => 0]);

                $transaction->update([
                    'stripe_subscription_id' => $subscription->id,
                    'stripe_customer_id' => $customer->id,
                    'stripe_payment_intent_id' => $session->payment_intent ?? null,
                    'payment_gateway' => 2,
                    'payment_type' => 2,
                    'payment_status' => 2, // Paid
                    'status' => 1, // Active
                    'end_date' => Carbon::createFromTimestamp($subscription->current_period_end),
                    'description' => 'Subscription activated successfully.',
                ]);
            } else {
                $subscriptionPackageIds = SubscriptionPackage::where('subscription_type', 'topup')->pluck('id')->toArray();
                PackageTrx::where('provider_id', $transaction['provider_id'])
                    ->whereIn('package_id', $subscriptionPackageIds)
                    ->update(['status' => 0]);

                $transaction->update([
                    'payment_gateway' => 2,
                    'payment_type' => 2,
                    'payment_status' => 2, // Paid
                    'status' => 1, // Active
                    'description' => 'Subscription activated successfully.',
                ]);
            }
        }

        return redirect()->route('provider.subscription');
    }
}
