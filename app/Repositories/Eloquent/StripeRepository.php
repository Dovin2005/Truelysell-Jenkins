<?php

namespace App\Repositories\Eloquent;

use Modules\GlobalSetting\Entities\GlobalSetting;
use Modules\GlobalSetting\app\Models\Currency;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Modules\Product\app\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
use App\Http\Controllers\Controller;

class StripeRepository implements StripeRepositoryInterface
{
    public function live_mobile(Request $request): JsonResponse
    {
        Stripe::setApiKey(config('stripe.test.sk'));
    
        // Validate that total_amount is provided
        $request->validate([
            'total_amount' => 'required|numeric|min:1'
        ]);
    
        // Convert total_amount to cents
        $amount = $request->input('total_amount') * 100;

        $currencyDetails = Currency::where('is_default', 1)->latest()->first();
    
        $paymentIntent = PaymentIntent::create([
            'amount' => $amount,
            'currency' => strtolower($currencyDetails->code ?? 'usd'),
            'payment_method_types' => ['card'],
        ]);
    
        return response()->json([
            'code' => 200,
            'message' => __('Subscription payment initiated successfully.'),
            'data' => [
                'transaction_id' => $request->input('transaction_id'), 
                'total_amount'   => $request->input('total_amount'),
                'client_secret'  => $paymentIntent->client_secret,
            ],
        ], 200);
    }

    public function live_mobile_pay(Request $request): JsonResponse
    {
        Stripe::setApiKey(config('stripe.test.sk'));

        $currencyDetails = Currency::where('is_default', 1)->latest()->first();

        $subscriptionPackage = SubscriptionPackage::where('id', $request->id)->firstOrFail();

        $currentDate = Carbon::now();
        $trxDate = $currentDate->toDateString();

        $endDate = match ($subscriptionPackage->package_term) {
            'day' => $currentDate->copy()->addDays($subscriptionPackage->package_duration)->toDateTimeString(),
            'month' => $currentDate->copy()->addMonths($subscriptionPackage->package_duration)->toDateTimeString(),
            'year' => $currentDate->copy()->addYears($subscriptionPackage->package_duration)->toDateTimeString(),
            default => null,
        };

        $transactionId = 'TXN' . strtoupper(uniqid());

        if ($request->type === 'free') {
            FacadesDB::table('package_transactions')->insert([
                'provider_id'    => $request->provider_id,
                'transaction_id' => $transactionId,
                'trx_date'       => $trxDate,
                'end_date'       => $endDate,
                'package_id'     => $subscriptionPackage->id,
                'amount'         => 0,
                'payment_status' => 2, // Free
                'created_by'     => $request->provider_id,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
    
            return response()->json([
                'code' => 200,
                'message' => __('Free subscription added successfully.'),
                'data' => [],
            ], 200);
        }

        FacadesDB::table('package_transactions')->insert([
            'provider_id'    => $request->provider_id,
            'transaction_id' => $transactionId,
            'trx_date'       => $trxDate,
            'end_date'       => $endDate,
            'package_id'     => $subscriptionPackage->id,
            'amount'         => $subscriptionPackage->price,
            'payment_status' => 1,
            'created_by'     => $request->provider_id,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        $paymentIntent = PaymentIntent::create([
            'amount'   => $subscriptionPackage->price * 100,
            'currency' => strtolower($currencyDetails->code ?? 'usd'),
            'payment_method_types' => ['card'],
        ]);

        return response()->json([
            'code' => 200,
            'message' => __('Subscription payment initiated successfully.'),
            'data' => [
                'transaction_id' => $transactionId,
                'package_title'  => $subscriptionPackage->package_title,
                'price'          => $subscriptionPackage->price,
                'client_secret'  => $paymentIntent->client_secret,
            ],
        ], 200);

        return response()->json([
            'code' => 200,
            'message' => __('Subscription added successfully.'),
            'data' => [],
        ], 200);
    }

    public function sub_payment_success(Request $request): JsonResponse
    {
        // Validate request
        $request->validate([
            'transaction_id' => 'required|exists:package_transactions,transaction_id',
        ]);

        // Find the transaction
        $transaction = FacadesDB::table('package_transactions')
            ->where('transaction_id', $request->transaction_id)
            ->first();

        if (!$transaction) {
            return response()->json(['code' => 404, 'message' => 'Transaction not found.'], 404);
        }

        // Update payment status to 2 (Successful)
        FacadesDB::table('package_transactions')
            ->where('transaction_id', $request->transaction_id)
            ->update([
                'payment_status' => 2,
                'updated_at'     => now(),
            ]);

        return response()->json([
            'code' => 200,
            'message' => 'Payment successful. Subscription activated.',
            'data' => [
                'transaction_id' => $request->transaction_id,
                'payment_status' => 2,
            ],
        ], 200);
    }

     public function test(Request $request): RedirectResponse
    {

        $stripekeydetails = GlobalSetting::orderBy('id', 'DESC')->where('key', 'stripe_key')->first();
        Stripe::setApiKey(config('stripe.test.sk'));
        $added_amount = 0;
        $service_offer = "";
        if (isset($request->service_offer)) {
            $service_offer = serialize($request->service_offer);
            foreach ($request->service_offer as $service_offerdValues) {
                $actualvalue = explode("_", $service_offerdValues);
                $added_amount = $added_amount + $actualvalue[1];
            }
        }

        $purchase_units = [];
        $currecy_details = Currency::orderBy('id', 'DESC')->where('is_default', 1)->first();
        $service_details = Product::orderBy('id', 'DESC')->where('id', $request->product_id)->first();
        $total_amount = $request->service_amount * $request->service_qty + ($added_amount) + ($request->amount_tax);
        $session = Session::create([
            'line_items'  => [
                [
                    'price_data' => [
                        'currency'     => $currecy_details->code ?? 'USD',
                        'product_data' => [
                            'name' => $service_details->source_name,
                        ],
                        'unit_amount'  => ($request->service_amount * $request->service_qty + ($added_amount) + ($request->amount_tax)) * 100,
                    ],
                    'quantity'   => 1,
                ],
            ],
            'mode'        => 'payment',
            'success_url' => route('success') . "?session_id={CHECKOUT_SESSION_ID}",
            'cancel_url'  => route('checkout'),
        ]);
        // print_r($session); exit();
        $data_insert = [
            'product_id' => $request->product_id,
            'service_qty' => $request->service_qty,
            'payment_type' => 2,
            'user_id' => Auth::user()->id,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'user_email' => $request->user_email,
            'user_phone' => $request->user_phone,
            'total_amount' => $total_amount,
            'payment_status' => 1,
            'amount_tax' => $request->amount_tax,
            'user_address' => $request->user_address,
            'user_city' => $request->user_city,
            'tranaction' => $session->id,
            'user_state' => $request->user_state,
            'user_postal' => $request->user_postalcode,
            'service_amount' => $request->service_amount,
            'notes' => $request->user_notes,
        ];
        $bookingId = DB::table('bookings')->insertGetId($data_insert);
        $provider_id = Product::select('created_by')->where('id', $request->product_id)->first();

        try {
            $pdfPath = \App\Helpers\InvoiceHelper::generateInvoice(
                $bookingId,
                $total_amount,
                '2',
                $provider_id->created_by,
            );
        } catch (\Exception $e) {
            Log::error("Failed to generate invoice: " . $e->getMessage());
        }

        return redirect()->away($session->url);
    }

    public function live(): RedirectResponse
    {
        Stripe::setApiKey(config('stripe.live.sk'));
        $session = Session::create([
            'line_items'  => [
                [
                    'price_data' => [
                        'currency'     => 'USD',
                        'product_data' => [
                            'name' => 'T-shirt',
                        ],
                        'unit_amount'  => 500,
                    ],
                    'quantity'   => 1,
                ],
            ],
            'mode'        => 'payment',
            'success_url' => route('success') . "?session_id={CHECKOUT_SESSION_ID}",
            'cancel_url'  => route('checkout'),
        ]);
        return redirect()->away($session->url);
    }

    public function paymentSuccess(Request $request): View|Factory|Application
    {
        Stripe::setApiKey(config('stripe.test.sk'));
        $sessionId = $request->get('session_id');
        Bookings::where('tranaction', $sessionId)->update(['payment_status' => 2]);
        //sendmail
        $getbookid = Bookings::select('id')->where('tranaction', $sessionId)->first();
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
                            '{{company_name}}' => $bookingdata['product']['createdBy']['userDetails']->company_name ?? "Truelysell",
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
                            Log::error('Error creating notification: ' . $e->getMessage());
                        }

                    }
                }
            }
        }
        return view('bookingsuccess');
    }

    public function stripepayment(Request $request): RedirectResponse
    {
        $stripeSecret = config('stripe.test.sk') ?? '';
        if (empty($stripeSecret)) {
            return redirect()->back()->with('error', 'Stripe is currently unavailable. Please choose another payment method.');
        }

        Stripe::setApiKey(config('stripe.test.sk'));
        $name = $request->package_name;
        $amount = $request->package_amount;
        $paymenttype = "";
        $subscriptionType = "regular";
        if (isset($request->trx_id)) {
            $packageTransaction = PackageTrx::where('id', $request->trx_id)->first();
            if ($packageTransaction) {
                $subscriptionType = SubscriptionPackage::where('id', $packageTransaction->package_id)->value('subscription_type');
            }
        }
        $successurl = route('provider.subscriptionsuccess') . "?session_id={CHECKOUT_SESSION_ID}&subscription_type=" . $subscriptionType;
        if (isset($request->type) && $request->type == 'user') {
            $name = $request->name;
            $amount = $request->amount;
            $paymenttype = $request->payment_type;
            $successurl = url('/user/stripepaymentsuccess' . "?session_id={CHECKOUT_SESSION_ID}");
        }
        if (isset($request->type) && $request->type == 'wallet') {
            $name = $request->type;
            $amount = $request->amount;
            $paymenttype = $request->payment_type;
            $successurl = route('user.walletsucess') . "?token={CHECKOUT_SESSION_ID}";
        }
        $currecy_details = Currency::orderBy('id', 'DESC')->where('is_default', 1)->first();

        $purchase_units = [];
        $session = Session::create([
            'line_items'  => [
                [
                    'price_data' => [
                        'currency'     => $currecy_details->code ?? 'USD',
                        'product_data' => [
                            'name' => $name,
                        ],
                        'unit_amount'  => intval($amount * 100),
                    ],
                    'quantity'   => 1,
                ],
            ],
            'mode'        => 'payment',
            'customer_creation' => 'always',
            'billing_address_collection' => 'required',
            'success_url' => $successurl,
            'cancel_url'  => route('checkout'),
        ]);

        if (isset($request->type) && $request->type == 'user') {
            Payments::where('id', $request->trx_id)->update(['transaction_id' =>  $session->id, 'payment_type' => $paymenttype]);
            try {
                $pdfPath = \App\Helpers\InvoiceHelper::generateInvoice(
                    $request->refid,
                    $request->amount,
                    '1',
                    $request->providerid,
                );
            } catch (\Exception $e) {
                Log::error("Failed to generate invoice: " . $e->getMessage());
            }
        } else if (isset($request->type) && $request->type == 'wallet') {
            WalletHistory::where('id', $request->trxId)->update(['transaction_id' =>  $session->id]);
        } else {
            PackageTrx::where('id', $request->trx_id)->update(['transaction_id' => $session->id]);
        }
        return redirect()->away($session->url);
    }

    public function subscriptionpaymentsuccess(Request $request): View|Factory|Application
    {
        Stripe::setApiKey(config('stripe.test.sk'));
        $sessionId = $request->get('session_id');
        $subscriptionType = $request->get('subscription_type');

        if ($subscriptionType == 'regular') {
            $Getuser_details = PackageTrx::where('transaction_id', $sessionId)->first();
            $subscriptionPackageIds = SubscriptionPackage::where('subscription_type', 'regular')->pluck('id')->toArray();
            PackageTrx::where('provider_id', $Getuser_details['provider_id'])
                ->whereIn('package_id', $subscriptionPackageIds)
                ->update(['status' => 0]);
        } else if ($subscriptionType == 'topup') {
            $Getuser_details = PackageTrx::where('transaction_id', $sessionId)->first();
            $subscriptionPackageIds = SubscriptionPackage::where('subscription_type', 'topup')->pluck('id')->toArray();
            PackageTrx::where('provider_id', $Getuser_details['provider_id'])
                ->whereIn('package_id', $subscriptionPackageIds)
                ->update(['status' => 0]);
        }

        PackageTrx::where('transaction_id', $sessionId)->update(['payment_status' => 2]);
        PackageTrx::where('transaction_id', $sessionId)->update(['status' => 1]);

        return view('provider.subscription.payment_success');
    }
}