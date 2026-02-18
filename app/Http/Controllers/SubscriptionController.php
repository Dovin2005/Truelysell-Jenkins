<?php

namespace App\Http\Controllers;

use App\Models\AddonModule;
use App\Repositories\Contracts\SubscriptionInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Modules\GlobalSetting\Entities\GlobalSetting;
use App\Models\PackageTrx;
use App\Services\PayPalSubscriptionService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Modules\GlobalSetting\app\Models\SubscriptionPackage;
use Stripe\StripeClient;

class SubscriptionController extends Controller
{
    protected $subscriptionRepository;

    public function __construct(SubscriptionInterface $subscriptionRepository)
    {
        $this->subscriptionRepository = $subscriptionRepository;
    }

    public function index(): View
    {
        $paymentMethods = $this->subscriptionRepository->getPaymentMethods(true);
        $adminBankDetails = GlobalSetting::whereIn('key', ['bank_name', 'account_number', 'branch_code', 'account_name'])
            ->pluck('value', 'key')
            ->toArray();

        $authUserId = Auth::id();
        $currentDate = now();

        $activeSubscription = PackageTrx::where('package_transactions.provider_id', $authUserId)
            ->where('package_transactions.status', 1)
            ->whereIn('package_transactions.payment_status', [2])
            ->whereNull('package_transactions.deleted_at')
            ->join('subscription_packages', 'subscription_packages.id', '=', 'package_transactions.package_id')
            ->where('subscription_packages.subscription_type', 'regular')
            ->orderBy('package_transactions.id', 'desc')
            ->select('package_transactions.*', 'subscription_packages.subscription_type')
            ->whereDate('package_transactions.end_date', '>=', $currentDate)
            ->first();
        return view('provider.subscription.list', compact('paymentMethods', 'adminBankDetails', 'activeSubscription'));
    }

    public function historyindex(): View
    {
        $authUserId = Auth::id() ?? Cache::get('provider_auth_id');

        $data = [
            'standardplan' => $this->subscriptionRepository->getActiveSubscription($authUserId, 'regular'),
            'topupplan' => $this->subscriptionRepository->getActiveSubscription($authUserId, 'topup'),
            'currency' => $this->subscriptionRepository->getCurrencySymbol()->symbol ?? '$'
        ];

        return view('provider.subscription.subscriptionhistory', compact('data'));
    }

    public function storepacktrx(Request $request): JsonResponse
    {
        try {
            $authUserId = Auth::id() ?? $request->provider_id ?? Cache::get('provider_auth_id');

            $data = [
                'provider_id' => $authUserId,
                'package_id' => $request->package_id,
                'amount' => $request->amount,
                'type' => $request->type ?? 'paid',
                'subscribetype' => $request->subscribetype ?? null
            ];

            $transactionId = $this->subscriptionRepository->createPackageTransaction($data);

            return response()->json([
                'code' => 200,
                'message' => 'Success',
                'data' => $transactionId
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getpaymentmethod(): JsonResponse
    {
        try {
            $methods = $this->subscriptionRepository->getPaymentMethods(false, false);
            return response()->json($methods->isNotEmpty() ? $methods : []);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong while saving!'
            ], 500);
        }
    }

    public function getpaymentmethodProvider(): JsonResponse
    {
        try {
            $methods = $this->subscriptionRepository->getPaymentMethods();

            return response()->json($methods);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong while fetching payment methods!'
            ], 500);
        }
    }

    public function getsubscriptionlist(): JsonResponse
    {
        try {
            $subscriptions = $this->subscriptionRepository->getAllSubscriptions();
            return response()->json([
                'code' => 200,
                'message' => __('Data retrieved successfully.'),
                'data' => $subscriptions,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getsubscriptionhistorylist(Request $request): JsonResponse
    {
        try {
            $authId = Auth::id() ?? $request->user_id;
            $data = $this->subscriptionRepository->getUserSubscriptionHistory($authId);

            return response()->json([
                'code' => 200,
                'message' => __('Data retrieved successfully.'),
                'data' => $data,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function updateBankTransfer(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'trx_id'        => 'required',
                'payment_proof' => 'required|file|mimes:jpg,jpeg,png|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'code'    => 422,
                    'message' => 'Validation Error',
                    'errors'  => $validator->errors()
                ], 422);
            }

            $trx_id = $request->trx_id;
            $data = [
                'payment_type' => 4,
                'payment_status' => 3,
                'status' => 1
            ];

            $file = $request->file('payment_proof');
            if ($file instanceof \Illuminate\Http\UploadedFile) {
                $filename = Str::uuid() . '_' . time() . '.' . $file->getClientOriginalExtension();
                $file->storeAs('subscription', $filename, 'public');
                $data['payment_proof'] = 'subscription/' . $filename;
            }

            $result = PackageTrx::where('id', $trx_id)->update($data);

            if ($result) {
                $updatedTrx = PackageTrx::find($trx_id);
                $subscriptionType = SubscriptionPackage::where('id', $updatedTrx->package_id)->value('subscription_type');

                if (in_array($subscriptionType, ['regular', 'topup'])) {
                    $subscriptionPackageIds = SubscriptionPackage::where('subscription_type', $subscriptionType)
                        ->pluck('id')
                        ->toArray();

                    PackageTrx::where('provider_id', $updatedTrx->provider_id)
                        ->whereIn('package_id', $subscriptionPackageIds)
                        ->where('id', '!=', $trx_id)
                        ->update(['status' => 0]);
                }
            }

            return response()->json([
                'code' => 200,
                'message' => "Payment Proof upload successfully and please wait for admin verification."
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => "Error while uploading payment proof!",
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function verifyBankTransferPayment(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'trx_id' => 'required',
            ]);

            $trx_id = $request->trx_id;
            PackageTrx::where('id', $trx_id)->update(['status' => 1, 'payment_status' => 2]);

            return response()->json([
                'code' => 200,
                'message' => "Activated successfully."
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => "Error while activating!",
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function cancelSubscription(Request $request)
    {
        try {
            $providerId = Auth::id() ?? $request->user_id;
            $activeSubscription = PackageTrx::where('package_transactions.provider_id', $providerId)
                ->where('package_transactions.status', 1)
                ->where('package_transactions.payment_status', 2)
                ->whereNull('package_transactions.deleted_at')
                ->join('subscription_packages', 'subscription_packages.id', '=', 'package_transactions.package_id')
                ->where('subscription_packages.subscription_type', 'regular')
                ->orderBy('package_transactions.id', 'desc')
                ->select('package_transactions.*', 'subscription_packages.subscription_type')
                ->first();
            if ($activeSubscription) {
                //determine the gateway to be used
                if ($activeSubscription->payment_gateway == 2 && $activeSubscription->stripe_subscription_id != '') {
                    //stripe
                    $stripe = new StripeClient(env('STRIPE_SECRET'));
                    $subscriptionId = $activeSubscription->stripe_subscription_id;
                    $remoteSub = $stripe->subscriptions->retrieve($subscriptionId, []);

                    $remoteStatus = $remoteSub->status ?? null;
                    if ($remoteStatus !== 'canceled') {
                        $cancelResult = $stripe->subscriptions->cancel($subscriptionId, []); // immediate cancel
                        Log::info('Stripe subscription canceled', [
                            'subscription' => $subscriptionId,
                            'cancel_result' => is_object($cancelResult) ? (array) $cancelResult : $cancelResult,
                        ]);

                        PackageTrx::where('provider_id', $providerId)
                            ->where('status', 1)
                            ->update([
                                'status' => 0,
                                'description' => "Deactivated due manual cancellation",
                            ]);
                    } else {
                        Log::info('Stripe subscription already canceled', ['subscription' => $subscriptionId]);
                    }
                } else if ($activeSubscription->payment_gateway == 1 &&  $activeSubscription->paypal_subscription_id != '') {
                    // paypal
                    $paypalService = app(PayPalSubscriptionService::class);
                    $result = $paypalService->cancelPaypalSubscription($activeSubscription->paypal_subscription_id);
                    if ($result) {
                        PackageTrx::where('provider_id', $providerId)
                            ->where('status', 1)
                            ->update([
                                'status' => 0,
                                'description' => "Deactivated due manual cancellation",
                            ]);
                    }
                } else {
                    // dd('no gateway');
                    PackageTrx::where('provider_id', $providerId)
                        ->where('status', 1)
                        ->update([
                            'status' => 0,
                            'description' => "Deactivated due manual cancellation",
                        ]);
                }
            }

            return redirect()->back()->with('success', 'Subscription cancelled successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
