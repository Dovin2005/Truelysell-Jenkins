<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Illuminate\Support\Facades\Redirect;
use Modules\Product\app\Models\Book;
use App\Models\PackageTrx;
use App\Models\Bookings;
use Illuminate\Support\Facades\DB;
use Modules\Leads\app\Models\Payments;
use Modules\Communication\app\Http\Controllers\EmailController;
use Modules\Communication\app\Models\Templates;
use Illuminate\Support\Str;
use Modules\GlobalSetting\Entities\GlobalSetting;
use Modules\GlobalSetting\app\Models\Currency;
use Modules\Communication\app\Http\Controllers\NotificationController;
use Illuminate\Support\Carbon;
use Modules\Product\app\Models\Product;
use App\Models\WalletHistory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\GlobalSetting\app\Models\SubscriptionPackage;
use App\Repositories\Contracts\PaypalRepositoryInterface;
use App\Services\PayPalSubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PaypalController extends Controller
{
    private $provider;
    protected $paypalRepository;
    private $baseUrl;

    public function __construct(PaypalRepositoryInterface $paypalRepository)
    {
        $this->provider = new PayPalClient;
        $this->provider->getAccessToken();
        $this->paypalRepository = $paypalRepository;
        $this->baseUrl = env('PAYPAL_MODE') === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    public function ProcessPayment(Request $request)
    {
        $response = $this->paypalRepository->ProcessPayment($request);
        return $response;
    }
    public function Successpayment(Request $request)
    {
        $response = $this->paypalRepository->Successpayment($request);
        return $response;
    }
    public function UserSuccesspayment(Request $request)
    {
        $response = $this->provider->capturePaymentOrder($request->get('token'));
        Payments::where('transaction_id', $response['id'])->update(['status' => 2]);

        return view('user.userpaymentsuccess');
    }
    public function WalletSuccesspayment(Request $request)
    {
        $response = $this->provider->capturePaymentOrder($request->get('token'));
        WalletHistory::where('transaction_id', $response['id'])->update(['status' => 'completed']);
        return view('user.walletpaymentsuccess');
    }

    public function handlePayment(Request $request)
    {
        $response = $this->paypalRepository->handlePayment($request);
        return $response;
    }

    public function handleBankPayment(Request $request)
    {
        $response = $this->paypalRepository->handleBankPayment($request);
        return $response;
    }
    public function handlecodPayment(Request $request)
    {
        $response = $this->paypalRepository->handlecodPayment($request);
        return $response;
    }

    public function handleWalletPayment(Request $request)
    {
        $response = $this->paypalRepository->handleWalletPayment($request);
        return $response;
    }

    public function paymentSuccess(Request $request)
    {
        $response = $this->provider->capturePaymentOrder($request->get('token'));
        Bookings::where('tranaction', $response['id'])->update(['payment_status' => 2]);
        //sendmail
        $getbookid = Bookings::select('id')->where('tranaction', $response['id'])->first();
        if (isset($getbookid)) {
            $bookingdata = Bookings::select(
                'bookings.*',
                DB::raw("
                CASE
                    WHEN bookings.payment_type = 1 THEN 'Paypal'
                    WHEN bookings.payment_type = 2 THEN 'Stripe'
                    WHEN bookings.payment_type = 3 THEN 'Razorpay'
                    WHEN bookings.payment_type = 4 THEN 'Bank Transfer'
                    WHEN bookings.payment_type = 5 THEN 'COD'
                    ELSE 'Unknown'
                END AS paymenttype"),
                DB::raw("DATE_FORMAT(bookings.created_at, '%d-%m-%Y') AS bookingdate"),
                DB::raw("TIME_FORMAT(bookings.from_time, '%H:%i') AS fromtime"),
                DB::raw("TIME_FORMAT(bookings.to_time, '%H:%i') AS totime"),
                'products.source_name',
                'users.name as user_name',
                'provider.name as provider_name',
                'provider.email as provideremail',
                'payout_history.id as refundid',
                'products.created_by',
                DB::raw("DATE_FORMAT(payout_history.created_at, '%d-%m-%Y') AS trxdate"),
                DB::raw("DATE_FORMAT(payout_history.updated_at, '%d-%m-%Y') AS refunddate")
            )
                ->leftJoin('products', 'products.id', '=', 'bookings.product_id')
                ->leftJoin('users', 'users.id', '=', 'bookings.user_id')
                ->leftJoin('users as provider', 'provider.id', '=', 'products.created_by')
                ->leftJoin('payout_history', 'payout_history.reference_id', '=', 'bookings.id')
                ->where('bookings.id', $getbookid->id)
                ->with(['user.userDetails', 'product.createdBy.userDetails'])
                ->first();

            $controller = new Controller();
            $notificationsettings = $controller->getnotificationsettings(1, 'Booking Success');
            if ($notificationsettings == 1) {
                $gettemplate = Templates::select('templates.subject', 'templates.content')->leftjoin('notification_types', 'notification_types.id', '=', 'templates.notification_type')->where('notification_types.type', 'Booking Success')->where('templates.type', 1)->where('templates.status', 1)->first();
                if (isset($gettemplate)) {
                    $tempdata = [];
                    $service = "";
                    if (isset($bookingdata)) {
                        $service = $bookingdata->source_name;
                        $tempdata = [
                            '{{user_name}}' => $bookingdata->user_name,
                            '{{booking_id}}' => $bookingdata->id,
                            '{{service_name}}' => $bookingdata->source_name,
                            '{{appointment_date}}' => $bookingdata->bookingdate,
                            '{{appointment_time}}' => $bookingdata->fromtime - $bookingdata->totime,
                            '{{team_name}}' => $bookingdata->provider_name,
                            '{{contact}}' => $bookingdata->provideremail,
                            '{{website_link}}' =>  $bookingdata['product']['createdBy']['userDetails']->company_website ?? "",
                            '{{company_name}}' => $bookingdata['product']['createdBy']['userDetails']->company_name ?? "Truelysell",
                            '{{service_address}}' => $bookingdata['product']['createdBy']['userDetails']->company_address ?? ""
                        ];

                        // Replace placeholders dynamically
                        $finalContent = Str::replace(array_keys($tempdata), array_values($tempdata), $gettemplate->content);
                        $subject = Str::replace('{{service_name}}', $service, $gettemplate->subject);

                        $data = [
                            'to_email' => $bookingdata->user_email,
                            'subject' => $subject,
                            'content' => $finalContent
                        ];

                        try {
                            // Create a new Request object
                            $request = new Request($data);
                            // Call sendEmail in EmailController
                            $emailController = new EmailController();
                            $emailController->sendEmail($request);
                        } catch (\Exception $e) {
                            Log::error('Error sending email: ' . $e->getMessage());
                        }
                    }
                }
            }
            /*Notification*/
            $notificationsettings = $controller->getnotificationsettings(3, 'Booking Success');
            if ($notificationsettings == 1) {
                $gettemplate = Templates::select('templates.subject', 'templates.content')->leftjoin('notification_types', 'notification_types.id', '=', 'templates.notification_type')->where('notification_types.type', 'Booking Success')->where('recipient_type', 2)->where('templates.type', 3)->where('templates.status', 1)->first();
                if (isset($gettemplate)) {
                    if (isset($bookingdata)) {
                        $fromtime = $bookingdata->fromtime ?? "";
                        $totime = $bookingdata->totime ?? "";
                        $tempdata = [];
                        $tempdata = [
                            '{{customer_name}}' => $bookingdata->user_name,
                            '{{booking_id}}' => $bookingdata->id,
                            '{{service_name}}' => $bookingdata->source_name,
                            '{{appointment_date}}' => $bookingdata->bookingdate,
                            '{{appointment_time}}' => $fromtime . ' ' . $totime,
                            '{{company_name}}' => $bookingdata['product']['createdBy']['userDetails']->company_name ?? "",
                            '{{service_address}}' => $bookingdata['product']['createdBy']['userDetails']->company_address ?? ""
                        ];
                        $todescription = Str::replace(array_keys($tempdata), array_values($tempdata), $gettemplate->content);
                        $getfromtemplate = Templates::select('templates.subject', 'templates.content')->leftjoin('notification_types', 'notification_types.id', '=', 'templates.notification_type')->where('notification_types.type', 'Booking Success')->where('recipient_type', 1)->where('templates.type', 3)->where('templates.status', 1)->first();
                        $fromdescription = "";
                        if (isset($getfromtemplate)) {
                            $fromtempdata = [];
                            $fromtempdata = [
                                '{{booking_id}}' => $bookingdata->id,
                                '{{service_name}}' => $bookingdata->source_name,
                                '{{appointment_date}}' => $bookingdata->bookingdate,
                                '{{appointment_time}}' => $fromtime . ' ' . $totime,
                                '{{provider_name}}' => $bookingdata->provider_name,
                            ];
                            $fromdescription = Str::replace(array_keys($fromtempdata), array_values($fromtempdata), $getfromtemplate->content);
                        }
                        $data = [
                            'communication_type' => '3',
                            'source' => 'Booking Success',
                            'reference_id' => $bookingdata->id,
                            'user_id' =>  $bookingdata->user_id,
                            'to_user_id' => $bookingdata->created_by,
                            'to_description' => $todescription,
                            'from_description' => $fromdescription
                        ];

                        try {
                            // Create a new Request object
                            $request = new Request($data);
                            // Call Storenotification  in NotificationController
                            $notification = new NotificationController();
                            $notification->Storenotification($request);
                        } catch (\Exception $e) {
                            Log::error('Error sending notification: ' . $e->getMessage());
                        }
                    }
                }
            }
        }
        return view('bookingsuccess');
    }

    public function paymentFailed()
    {
        dd('Your payment has been canceled. Cancellation page goes here.');
    }

    // subscriptionCheckout
    public function subscriptionCheckout(Request $request, PayPalSubscriptionService $payPalSubscriptionService)
    {
        $packageTrxId = $request->trx_id;
        $packageTrx = PackageTrx::find($packageTrxId);
        if (!$packageTrx) {
            return response()->json(['message' => 'Invalid package transaction.'], 422);
        }

        $package = SubscriptionPackage::find($packageTrx->package_id);

        if (!$package) {
            return response()->json(['message' => 'Invalid package.'], 422);
        }

        $subscriptionType = $package->subscription_type ?? '';

        if ($subscriptionType == 'regular' && $package->paypal_plan_id && $package->paypal_recurring) {
            $provider = Auth::user();
            $accessToken = $payPalSubscriptionService->getAccessToken();
            $companyName = GlobalSetting::where('key', 'company_name')->value('value');

            // Create the subscription request payload
            $payload = [
                'plan_id' => $package->paypal_plan_id,
                'custom_id' => $package->id . '-' . $provider->id,
                'application_context' => [
                    'brand_name' => $companyName ?? 'Truelysell',
                    'locale' => 'en-US',
                    'shipping_preference' => 'NO_SHIPPING',
                    'user_action' => 'SUBSCRIBE_NOW',
                    'return_url' => route('paypal.subscription.success') . '?subscription_type=' . $subscriptionType . '&is_recurring=true',
                    'cancel_url' => route('paypal.subscription.cancel'),
                ],
            ];

            // Send API request to PayPal
            $response = Http::withToken($accessToken)
                ->post($this->baseUrl . '/v1/billing/subscriptions', $payload);
            $data = $response->json();

            if (!$response->successful()) {
                return response()->json([
                    'message' => 'Failed to create PayPal subscription.',
                    'error' => $data
                ], 500);
            }
            // Find approval link
            $approvalUrl = collect($data['links'] ?? [])->firstWhere('rel', 'approve')['href'] ?? null;

            if (!$approvalUrl) {
                return response()->json(['message' => 'Approval URL not found'], 500);
            }

            // Create pending package transaction record (same as Stripe logic)
            PackageTrx::where('id', $packageTrxId)->update([
                'transaction_id' => $data['id'],
                'payment_gateway' => 1, // 1 = PayPal
                'payment_status' => 1, // 1 = Unpaid
                'payment_type' => 1,
                'status' => 0, // inactive until payment confirmed
                'created_by' => $provider->id,
                'description' => 'PayPal subscription initiated.',
            ]);
            return response()->json([
                'success' => true,
                'url' => $approvalUrl,
                'message' => 'Redirect to PayPal for approval.'
            ]);
        } else {
            return $this->createTopupCheckoutSession($request, $subscriptionType, $package->id);
        }
    }

    private function createTopupCheckoutSession(Request $request, $subscriptionType, $packageId)
    {
        $packageTrxId = $request->trx_id;
        $package = SubscriptionPackage::find($packageId);
        $providerId = Auth::id();

        $defaultCurrency = getDefaultCurrencyCode();

        $order['intent'] = 'CAPTURE';
        $purchase_units = [];
        $unit = [
            'items' => [
                [
                    'name' => $package->package_title,
                    'quantity' => 1,
                    'unit_amount' => [
                        'currency_code' => $defaultCurrency,
                        'value' => $package->price
                    ]
                ],

            ],
            'amount' => [
                'currency_code' => $defaultCurrency,
                'value' => $package->price,
                'breakdown' => [
                    'item_total' => [
                        'currency_code' => $defaultCurrency,
                        'value' => $package->price
                    ],
                ]
            ]
        ];

        $purchase_units[] = $unit;

        $order['purchase_units'] = $purchase_units;

        $order['application_context'] = [
            'return_url' => route('paypal.subscription.success') . '?subscription_type=' . $subscriptionType . '&is_recurring=false',
            'cancel_url' => url('payment-failed')
        ];

        $response = $this->provider->createOrder($order);

        if (!is_array($response) || !array_key_exists('id', $response)) {
            return response()->json([
                'code' => 422,
                'message' => 'PayPal is currently unavailable. Please choose another payment method.'
            ], 422);
        }

        PackageTrx::where('id', $packageTrxId)->update([
            'transaction_id' => $response['id'],
            'payment_gateway' => 1, // 1 = PayPal
            'payment_status' => 1, // 1 = Unpaid
            'payment_type' => 1,
            'status' => 0, // inactive until payment confirmed
            'created_by' => $providerId,
            'description' => 'PayPal subscription initiated.',
        ]);

        $approvalUrl = $response['links'][1]['href'];

        return response()->json([
            'success' => true,
            'url' => $approvalUrl,
            'message' => 'Redirect to PayPal for approval.'
        ]);
    }

    public function subscriptionSuccess(Request $request, PayPalSubscriptionService $payPalService)
    {
        $subscriptionType = $request->get('subscription_type');
        $isRecurring = $request->get('is_recurring');

        if ($subscriptionType == 'regular' && $isRecurring == 'true') {

            $subscriptionId = $request->query('subscription_id');
            $provider = Auth::user();

            if (!$subscriptionId) {
                return redirect()->route('provider.subscription')->with('error', 'Subscription ID missing.');
            }

            // Get access token
            $accessToken = $payPalService->getAccessToken();

            //Retrieve full subscription details from PayPal API
            $response = Http::withToken($accessToken)
                ->get($this->baseUrl . '/v1/billing/subscriptions/' . $subscriptionId);

            if (!$response->successful()) {
                return redirect()->route('provider.subscription')
                    ->with('error', 'Failed to verify PayPal subscription.');
            }
            $data = $response->json();

            $transaction = PackageTrx::where('transaction_id', $data['id'])->first();
            $subscriptionPackageIds = SubscriptionPackage::where('subscription_type', 'regular')->pluck('id')->toArray();
            PackageTrx::where('provider_id', $transaction['provider_id'])
                ->whereIn('package_id', $subscriptionPackageIds)
                ->update(['status' => 0]);

            // Update DB with verified subscription
            $transaction->update([
                'paypal_subscription_id' => $subscriptionId,
                'payment_gateway' => 1,
                'payment_status' => 2, // Paid
                'status' => 1, // Active
                'description' => "PayPal subscription activated",
            ]);

        } else {
            $response = $this->provider->capturePaymentOrder($request->get('token'));
            $transaction = PackageTrx::where('transaction_id', $response['id'])->first();
            $subscriptionPackageIds = SubscriptionPackage::where('subscription_type', $subscriptionType)->pluck('id')->toArray();
            PackageTrx::where('provider_id', $transaction['provider_id'])
                ->whereIn('package_id', $subscriptionPackageIds)
                ->update(['status' => 0]);

            // Update DB with verified subscription
            $transaction->update([
                'payment_gateway' => 1,
                'payment_status' => 2, // Paid
                'status' => 1, // Active
                'description' => "PayPal subscription activated",
            ]);
        }

        // Redirect user back
        return redirect()->route('provider.subscription')
            ->with('success', 'PayPal subscription activated successfully!');
    }
}
