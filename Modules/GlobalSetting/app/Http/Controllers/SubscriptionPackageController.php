<?php

namespace Modules\GlobalSetting\app\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Modules\GlobalSetting\app\Models\Currency;
use Modules\GlobalSetting\app\Models\SubscriptionPackage;
use Modules\GlobalSetting\app\Repositories\Contracts\SubscriptionPackageInterface;
use Modules\GlobalSetting\app\Http\Requests\StoreSubscriptionPackageRequest;
use Modules\GlobalSetting\app\Http\Requests\UpdateSubscriptionPackageRequest;
use Modules\GlobalSetting\app\Http\Requests\DeleteSubscriptionPackageRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use App\Models\PackageTrx;
use App\Services\PayPalSubscriptionService;
use Illuminate\Support\Facades\DB;
use Stripe\Product as StripeProduct;
use Stripe\Stripe;
use Stripe\Price as StripePrice;

class SubscriptionPackageController extends Controller
{
    public function __construct(
        protected SubscriptionPackageInterface $subscriptionPackageRepository
    ) {
    }

    public function index(): JsonResponse
    {
        $filters = request()->only(['subscriptiontype', 'order_by', 'sort_by']);
        $subscriptions = $this->subscriptionPackageRepository->index($filters);

        if ($subscriptions->isEmpty()) {
            return $this->jsonResponse(200, 'Package Not Found!', []);
        }

        $currency = Cache::remember('currency_details', 86400, function () {
            return Currency::orderBy('id', 'DESC')->where('is_default', 1)->first();
        });

        $currencySymbol = $currency->symbol ?? '$';
        $authUserId = request('authid') ?? Auth::id();
        $currentDate = now();

        $latestRegularSubscription = PackageTrx::where('package_transactions.provider_id', $authUserId)
            ->where('package_transactions.status', 1)
            ->whereIn('package_transactions.payment_status', [2, 3])
            ->whereNull('package_transactions.deleted_at')
            ->join('subscription_packages', 'subscription_packages.id', '=', 'package_transactions.package_id')
            ->where('subscription_packages.subscription_type', 'regular')
            ->orderBy('package_transactions.id', 'desc')
            ->select('package_transactions.*', 'subscription_packages.subscription_type')
            ->whereDate('package_transactions.end_date', '>=', $currentDate)
            ->first();
        
        $latestTopupSubscription = PackageTrx::where('package_transactions.provider_id', $authUserId)
            ->where('package_transactions.status', 1)
            ->whereIn('package_transactions.payment_status', [2, 3])
            ->whereNull('package_transactions.deleted_at')
            ->join('subscription_packages', 'subscription_packages.id', '=', 'package_transactions.package_id')
            ->where('subscription_packages.subscription_type', 'topup')
            ->orderBy('package_transactions.id', 'desc')
            ->select('package_transactions.*', 'subscription_packages.subscription_type')
            ->whereDate('package_transactions.end_date', '>=', $currentDate)
            ->first();
            
        $data = $subscriptions->map(function ($subscription) use ($currencySymbol, $latestRegularSubscription, $latestTopupSubscription) {
            $subscribedStatus = 0;
            $topupSubscribedStatus = 0;
            if ($latestRegularSubscription && $latestRegularSubscription->package_id == $subscription->id) {
                $subscribedStatus = $latestRegularSubscription->payment_status == 2 ? 1 : 2;
            }
            if ($latestTopupSubscription && $latestTopupSubscription->package_id == $subscription->id) {
                $topupSubscribedStatus = $latestTopupSubscription->payment_status == 2 ? 1 : 2;
            }

            return [
                'id' => $subscription->id,
                'package_title' => $subscription->package_title,
                'price' => $subscription->price,
                'package_term' => $subscription->package_term,
                'package_duration' => $subscription->package_duration,
                'number_of_service' => $subscription->number_of_service,
                'number_of_feature_service' => $subscription->number_of_feature_service,
                'number_of_product' => $subscription->number_of_product,
                'number_of_service_order' => $subscription->number_of_service_order,
                'number_of_locations' => $subscription->number_of_locations,
                'number_of_staff' => $subscription->number_of_staff,
                'subscription_type' => $subscription->subscription_type,
                'description' => $subscription->description,
                'status' => $subscription->status,
                'currency' => $currencySymbol,
                'subscribedstatus' => $subscribedStatus,
                'topup_subscribedstatus' => $topupSubscribedStatus
            ];
        })->toArray(); // Convert the Collection to array here

        return $this->jsonResponse(200, 'Subscription details retrieved successfully.', $data);
    }

    public function listAll(): JsonResponse
    {
        $subscriptions = SubscriptionPackage::get();

        if ($subscriptions->isEmpty()) {
            return $this->jsonResponse(200, 'Package Not Found!', []);
        }

        $currency = Cache::remember('currency_details', 86400, function () {
            return Currency::orderBy('id', 'DESC')->where('is_default', 1)->first();
        });

        $currencySymbol = $currency->symbol ?? '$';
        $authUserId = request('authid') ?? Auth::id();

        $data = $subscriptions->map(function ($subscription) use ($currencySymbol) {
            return [
                'id' => $subscription->id,
                'package_title' => $subscription->package_title,
                'price' => $subscription->price,
                'package_term' => $subscription->package_term,
                'package_duration' => $subscription->package_duration,
                'number_of_service' => $subscription->number_of_service,
                'number_of_feature_service' => $subscription->number_of_feature_service,
                'number_of_product' => $subscription->number_of_product,
                'number_of_service_order' => $subscription->number_of_service_order,
                'number_of_locations' => $subscription->number_of_locations,
                'number_of_staff' => $subscription->number_of_staff,
                'subscription_type' => $subscription->subscription_type,
                'description' => $subscription->description,
                'status' => $subscription->status,
                'currency' => $currencySymbol,
                'featured' => $subscription->featured,
                'badge' => $subscription->badge,
                'promoted_jobber' => $subscription->promoted_jobber,
                'promoted_service' => $subscription->promoted_service,
                'stripe_recurring' => $subscription->stripe_recurring,
                'paypal_recurring' => $subscription->paypal_recurring
            ];
        })->toArray(); // Convert the Collection to array here

        return $this->jsonResponse(200, 'Subscription details retrieved successfully.', $data);
    }

    public function store(StoreSubscriptionPackageRequest $request, PayPalSubscriptionService $paypal): JsonResponse
    {
        $existing = SubscriptionPackage::where('package_title', $request->package_title)->first();

        if ($existing) {
            return $this->jsonResponse(422, 'Package Title already exists!');
        }

        $stripeRecurring = $request->stripe_recurring ?? 0;
        $paypalRecurring = $request->paypal_recurring ?? 0;

        if ($stripeRecurring == 1) {
            if (env('STRIPE_SECRET') == '' || env('STRIPE_KEY') == '' || env('STRIPE_WEBHOOK_SECRET') == '') {
                return $this->jsonResponse(422, 'Please add Stripe credentials.');
            }
        }

        if ($paypalRecurring == 1) {
            if (env('PAYPAL_SANDBOX_CLIENT_ID') == '' || env('PAYPAL_SANDBOX_CLIENT_SECRET') == '') {
                return $this->jsonResponse(422, 'Please add Paypal credentials.');
            }
        }

        DB::beginTransaction();
        try {
            $data = [
                'package_title' => $request->package_title,
                'price' => $request->price,
                'package_term' => $request->package_term,
                'package_duration' => $request->package_term == 'yearly' ? 1 : $request->package_duration,
                'number_of_service' => $request->number_of_service ?? 0,
                'number_of_feature_service' => $request->number_of_feature_service ?? 0,
                'number_of_product' => $request->number_of_product ?? 0,
                'number_of_service_order' => $request->number_of_service_order ?? 0,
                'number_of_locations' => $request->number_of_locations ?? 0,
                'number_of_staff' => $request->number_of_staff ?? 0,
                'subscription_type' => $request->subscription_type,
                'description' => $request->description,
                'status' => $request->status ?? 1,
                'featured' => $request->featured ?? 0,
                'badge' => $request->badge ?? 0,
                'promoted_service' => $request->promoted_service ?? 0,
                'promoted_jobber' => $request->promoted_jobber ?? 0,
                'stripe_recurring' => $stripeRecurring,
                'paypal_recurring' => $paypalRecurring
            ];

            $package = $this->subscriptionPackageRepository->store($data);

            if (! $package) {
                DB::rollBack();
                return $this->jsonResponse(500, 'Something went wrong while saving the package!');
            }

            // Only for recurring subscriptions
            if ($package->subscription_type === 'regular' && $package->package_term !== 'lifetime') {

                if ($stripeRecurring) {
                    $currency = getDefaultCurrencyCode();
                    // === Stripe Creation ===
                    Stripe::setApiKey(env('STRIPE_SECRET'));
    
                    $product = StripeProduct::create([
                        'name' => $package->package_title,
                        'description' => $package->description ?? 'Subscription package',
                    ]);
    
                    $price = StripePrice::create([
                        'unit_amount' => $package->price * 100,
                        'currency' => $currency,
                        'recurring' => [
                            'interval' => $this->mapInterval($package->package_term),
                            'interval_count' => $package->package_duration ?? 1,
                        ],
                        'product' => $product->id,
                    ]);

                    // Update local DB
                    $package->update([
                        'stripe_product_id' => $product->id,
                        'stripe_price_id' => $price->id
                    ]);
                }

                if ($paypalRecurring) {
                    // === PayPal Creation ===
                    $productRes = $paypal->createProduct($package->package_title, $package->description ?? 'Subscription package');
                    $planRes = $paypal->createPlan(
                        $productRes['id'],
                        $package->package_title,
                        $package->price,
                        strtoupper($this->mapInterval($package->package_term)),
                        $package->package_duration ?? 1
                    );

                    $package->update([
                        'paypal_product_id' => $productRes['id'] ?? null,
                        'paypal_plan_id' => $planRes['id'] ?? null,
                    ]);
                    
                }
            }
            DB::commit();
            return $this->jsonResponse(200, __('subscription_package_create_success'));
        } catch (\Throwable $e) {
            DB::rollBack();

            // cleanup optional
            if (isset($product) && $product?->id) {
                try {
                    StripeProduct::update($product->id, ['active' => false]);
                } catch (\Throwable $inner) {
                }
            }

            return $this->jsonResponse(500, 'Error: ' . $e->getMessage());
        }
    }

    private function mapInterval(string $term): string
    {
        return match ($term) {
            'day' => 'day',
            'month' => 'month',
            'yearly' => 'year',
            default => 'month',
        };
    }

    public function update(UpdateSubscriptionPackageRequest $request, PayPalSubscriptionService $paypal): JsonResponse
    {
        $stripeRecurring = $request->edit_stripe_recurring ?? 0;
        $paypalRecurring = $request->edit_paypal_recurring ?? 0;

        if ($stripeRecurring == 1) {
            if (env('STRIPE_SECRET') == '' || env('STRIPE_KEY') == '' || env('STRIPE_WEBHOOK_SECRET') == '') {
                return $this->jsonResponse(422, 'Please add Stripe credentials.');
            }
        }

        if ($paypalRecurring == 1) {
            if (env('PAYPAL_SANDBOX_CLIENT_ID') == '' || env('PAYPAL_SANDBOX_CLIENT_SECRET') == '') {
                return $this->jsonResponse(422, 'Please add Paypal credentials.');
            }
        }

        /** @var SubscriptionPackage $package */
        $package = SubscriptionPackage::findOrFail($request->edit_id);

        $oldPrice  = $package->price;
        $oldTerm   = $package->package_term;
        $oldDur    = $package->package_duration;

        $data = [
            'package_title' => $request->edit_package_title,
            'price' => $request->edit_price,
            'package_term' => $request->edit_package_term,
            'package_duration' => $request->edit_package_term == 'yearly' ? 1 : $request->edit_package_duration,
            'number_of_service' => $request->edit_number_of_service ?? 0,
            'number_of_feature_service' => $request->edit_number_of_feature_service ?? 0,
            'number_of_product' => $request->edit_number_of_product ?? 0,
            'number_of_service_order' => $request->edit_number_of_service_order ?? 0,
            'number_of_locations' => $request->edit_number_of_locations ?? 0,
            'number_of_staff' => $request->edit_number_of_staff ?? 0,
            'subscription_type' => $request->edit_subscription_type,
            'description' => $request->edit_description,
            'status' => $request->status,
            'featured' => $request->featured ?? 0,
            'badge' => $request->badge ?? 0,
            'promoted_service' => $request->promoted_service ?? 0,
            'promoted_jobber' => $request->promoted_jobber ?? 0,
            'stripe_recurring' => $stripeRecurring,
            'paypal_recurring' => $paypalRecurring
        ];

        DB::beginTransaction();

        try {

            // ------------------------------------------
            // 1. Update Local DB Base Fields
            // ------------------------------------------
            $package->update($data);

            // ------------------------------------------
            // 2. If type != regular OR term == lifetime => no billing update
            // ------------------------------------------
            if ($package->subscription_type !== 'regular' || $package->package_term == 'lifetime') {
                DB::commit();
                return $this->jsonResponse(200, __('subscription_package_update_success'));
            }

            // ------------------------------------------
            // 3. Check if billing cycle OR price changed
            // ------------------------------------------
            $billingChanged =
                $oldPrice != $package->price ||
                $oldTerm != $package->package_term ||
                $oldDur != $package->package_duration;

            $emptyCheck = $package->stripe_product_id == null || $package->stripe_product_id == '' ||
                $package->paypal_product_id == null || $package->paypal_product_id == '';

            if ($billingChanged) {

                if ($package->stripe_product_id && $stripeRecurring == 1) {
                    Stripe::setApiKey(env('STRIPE_SECRET'));

                    $currencyCode = getDefaultCurrencyCode();
    
                    // ------------------------------------------
                    // 4. Create NEW Stripe Price
                    // ------------------------------------------
                    $newPrice = StripePrice::create([
                        'unit_amount' => $package->price * 100,
                        'currency' => $currencyCode,
                        'recurring' => [
                            'interval' => $this->mapInterval($package->package_term),
                            'interval_count' => $package->package_duration ?? 1,
                        ],
                        'product' => $package->stripe_product_id,
                    ]);
    
                    // Deactivate old price
                    if ($package->stripe_price_id) {
                        StripePrice::update($package->stripe_price_id, ['active' => false]);
                    }

                    $package->update([
                        'stripe_price_id' => $newPrice->id
                    ]);
                }

                // ------------------------------------------
                // 5. Create NEW PayPal Plan
                // ------------------------------------------
                $productId = $package->paypal_product_id;

                if (!$productId && $paypalRecurring == 1) {
                    // Create only if missing
                    $productRes = $paypal->createProduct($package->package_title, $package->description);
                    $productId = $productRes['id'];
                    $planRes = $paypal->createPlan(
                        $productId,
                        $package->package_title,
                        $package->price,
                        strtoupper($this->mapInterval($package->package_term)),
                        $package->package_duration
                    );

                    $package->update([
                        'paypal_plan_id' => $planRes['id'] ?? null
                    ]);
                }
            } else if ($emptyCheck) {
                if ($package->stripe_product_id == null || $package->stripe_product_id == '' && $stripeRecurring == 1) {
                    $currency = getDefaultCurrencyCode();
                    // === Stripe Creation ===
                    Stripe::setApiKey(env('STRIPE_SECRET'));
    
                    $product = StripeProduct::create([
                        'name' => $package->package_title,
                        'description' => $package->description ?? 'Subscription package',
                    ]);
    
                    $price = StripePrice::create([
                        'unit_amount' => $package->price * 100,
                        'currency' => $currency,
                        'recurring' => [
                            'interval' => $this->mapInterval($package->package_term),
                            'interval_count' => $package->package_duration ?? 1,
                        ],
                        'product' => $product->id,
                    ]);

                    // Update local DB
                    $package->update([
                        'stripe_product_id' => $product->id,
                        'stripe_price_id' => $price->id
                    ]);
                }

                $productId = $package->paypal_product_id;
                if ($productId == null || $productId == '' && $paypalRecurring == 1) {
                    $productRes = $paypal->createProduct($package->package_title, $package->description);
                    $productId = $productRes['id'];
                    $planRes = $paypal->createPlan(
                        $productId,
                        $package->package_title,
                        $package->price,
                        strtoupper($this->mapInterval($package->package_term)),
                        $package->package_duration
                    );

                    $package->update([
                        'paypal_product_id' => $productId,
                        'paypal_plan_id' => $planRes['id'] ?? null
                    ]);
                }
            }

            DB::commit();
            return $this->jsonResponse(200, __('subscription_package_update_success'));

        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->jsonResponse(500, "Error: " . $e->getMessage());
        }
    }

    public function delete(DeleteSubscriptionPackageRequest $request): JsonResponse
    {
        $this->subscriptionPackageRepository->delete($request->id);
        return $this->jsonResponse(200, __('subscription_package_delete_success'));
    }

    protected function jsonResponse(int $code, string $message, $data = []): JsonResponse
    {
        $responseData = $data instanceof \Illuminate\Support\Collection ? $data->toArray() : $data;
        
        return response()->json([
            'code' => $code,
            'message' => $message,
            'data' => $responseData
        ], $code);
    }
}