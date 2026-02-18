<?php

namespace App\Repositories\Eloquent;

use App\Models\Contact;
use App\Repositories\Contracts\PaypalRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Modules\GlobalSetting\app\Models\SubscriptionPackage;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Illuminate\Support\Facades\Redirect;
use App\Models\PackageTrx;
use App\Models\Bookings;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Leads\app\Models\Payments;
use Modules\GlobalSetting\app\Models\Currency;
use Modules\Product\app\Models\Product;
use App\Models\WalletHistory;
use Illuminate\Support\Facades\Log;

class PaypalRepository implements PaypalRepositoryInterface
{
    protected $model;
    private $provider;

    public function __construct(Contact $model)
    {
        $this->provider = new PayPalClient;
        $this->provider->getAccessToken();
        $this->model = $model;
    }

    public function ProcessPayment(Request $request)
    {
        $defaultCurrency = getDefaultCurrencyCode();

        $order['intent'] = 'CAPTURE';
        $purchase_units = [];
        $unit = [
            'items' => [
                [
                    'name' => $request->name,
                    'quantity' => 1,
                    'unit_amount' => [
                        'currency_code' => $defaultCurrency,
                        'value' => $request->service_amount
                    ]
                ],

            ],
            'amount' => [
                'currency_code' => $defaultCurrency,
                'value' => $request->service_amount,
                'breakdown' => [
                    'item_total' => [
                        'currency_code' => $defaultCurrency,
                        'value' => $request->service_amount
                    ],
                ]
            ]
        ];

        $purchase_units[] = $unit;

        $order['purchase_units'] = $purchase_units;
        if (isset($request->type) && $request->type == 'user') {
            $order['application_context'] = [
                'return_url' => url('/user/paymentsuccess'),
                'cancel_url' => url('payment-failed')
            ];
        } else if (isset($request->type) && $request->type == 'wallet') {
            $order['application_context'] = [
                'return_url' => url('/user/walletsucess'),
                'cancel_url' => url('payment-failed')
            ];
        } else {
            $subscriptionType = "regular";
            if (isset($request->trx_id)) {
                $packageTransaction = PackageTrx::where('id', $request->trx_id)->first();
                if ($packageTransaction) {
                    $subscriptionType = SubscriptionPackage::where('id', $packageTransaction->package_id)->value('subscription_type');
                }
            }
            $order['application_context'] = [
                'return_url' => url('/provider/paymentsuccess' . "?subscription_type=" . $subscriptionType),
                'cancel_url' => url('payment-failed')
            ];
        }
        $response = $this->provider->createOrder($order);

        if (!is_array($response) || !array_key_exists('id', $response)) {
            return response()->json([
                'code' => 422,
                'message' => 'PayPal is currently unavailable. Please choose another payment method.'
            ], 422);
        }

        try {
            if (isset($request->type) && $request->type == 'user') {
                Payments::where('id', $request->trx_id)->update(['transaction_id' =>  $response['id'], 'payment_type' => $request->paymenttype]);
                if ($response['id']) {
                    try {
                        $pdfPath = \App\Helpers\InvoiceHelper::generateInvoice(
                            $request->refid,
                            $request->service_amount,
                            '1',
                            $request->providerid,
                        );
                    } catch (\Exception $e) {
                        Log::error("Failed to generate invoice: " . $e->getMessage());
                    }
                }
            } else if (isset($request->type) && $request->type == 'wallet') {
                WalletHistory::where('id', $request->trx_id)->update(['transaction_id' =>  $response['id'], 'status' => 'pending']);
            } else {
                PackageTrx::where('id', $request->trx_id)->update(['transaction_id' =>  $response['id']]);
            }
            $approve_paypal_url = $response['links'][1]['href'];
            return $approve_paypal_url;
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function Successpayment(Request $request): View
    {
        $response = $this->provider->capturePaymentOrder($request->get('token'));
        $subscriptionType = $request->get('subscription_type');

        if ($subscriptionType == 'regular') {
            $Getuser_details = PackageTrx::where('transaction_id', $response['id'])->first();
            $subscriptionPackageIds = SubscriptionPackage::where('subscription_type', 'regular')->pluck('id')->toArray();
            PackageTrx::where('provider_id', $Getuser_details['provider_id'])
                ->whereIn('package_id', $subscriptionPackageIds)
                ->update(['status' => 0]);
        } else if ($subscriptionType == 'topup') {
            $Getuser_details = PackageTrx::where('transaction_id', $response['id'])->first();
            $subscriptionPackageIds = SubscriptionPackage::where('subscription_type', 'topup')->pluck('id')->toArray();
            PackageTrx::where('provider_id', $Getuser_details['provider_id'])
                ->whereIn('package_id', $subscriptionPackageIds)
                ->update(['status' => 0]);
        }
        PackageTrx::where('transaction_id', $response['id'])->update(['payment_status' => 2, 'status' => 1]);
        return view('provider.subscription.payment_success');
    }

    public function handlePayment(Request $request)
    {
        $order['intent'] = 'CAPTURE';

        $currecy_details = Currency::orderBy('id', 'DESC')->where('is_default', 1)->first();
        $defaultCurrency = getDefaultCurrencyCode();

        // $currency = Bookings::create($data_insert);
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
        $book_details = Bookings::orderBy('id', 'DESC')->first();
        //  print_r($book_details); exit();
        $total_amount = $request->service_amount * $request->service_qty + ($added_amount) + ($request->amount_tax);

        $unit = [
            'items' => [
                [
                    'name' => 'truelysell service',
                    'quantity' => 1,
                    'unit_amount' => [
                        'currency_code' => $defaultCurrency,
                        'value' => ($request->service_amount * $request->service_qty) + $added_amount + $request->amount_tax
                    ]
                ],

            ],
            'amount' => [
                'currency_code' => $defaultCurrency,
                'value' => ($request->service_amount * $request->service_qty) + $added_amount + $request->amount_tax,
                'breakdown' => [
                    'item_total' => [
                        'currency_code' => $defaultCurrency,
                        'value' => ($request->service_amount * $request->service_qty) + $added_amount + $request->amount_tax
                    ],
                ]
            ]
        ];

        $purchase_units[] = $unit;

        $order['purchase_units'] = $purchase_units;

        $order['application_context'] = [
            'return_url' => url('payment-success'),
            'cancel_url' => url('payment-failed')
        ];

        $response = $this->provider->createOrder($order);

        $data_insert = [
            'product_id' => $request->product_id,
            'service_qty' => $request->service_qty,
            'payment_type' => 1,
            'amount_tax' => $request->amount_tax,
            'first_name' => $request->first_name,
            'user_id' => Auth::user()->id,
            'last_name' => $request->last_name,
            'user_email' => $request->user_email,
            'total_amount' => $total_amount,
            'user_phone' => $request->user_phone,
            'service_offer' => $service_offer,
            'tranaction' =>  $response['id'],
            'user_address' => $request->user_address,
            'user_city' => $request->user_city,
            'user_state' => $request->user_state,
            'user_postal' => $request->user_postalcode,
            'service_amount' => $request->service_amount,
            'notes' => $request->user_notes,
        ];
        $bookingId = DB::table('bookings')->insertGetId($data_insert);
        $provider_id = Product::select('created_by')->where('id', $request->product_id)->first();
        try {
            // Call the InvoiceHelper directly
            $pdfPath = \App\Helpers\InvoiceHelper::generateInvoice(
                $bookingId,
                $total_amount,
                '2',
                $provider_id->created_by,
            );
        } catch (\Exception $e) {
            Log::error("Failed to generate invoice: " . $e->getMessage());
        }

        try {
            $approve_paypal_url = $response['links'][1]['href'];
            return Redirect::to($approve_paypal_url);
        } catch (\Throwable $th) {
            Log::error("Failed to redirect to paypal: " . $e->getMessage());
        }
    }

    public function handleBankPayment(Request $request)
    {
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
        $book_details = Bookings::orderBy('id', 'DESC')->first();



        $data_insert = [
            'product_id' => $request->product_id,
            'service_qty' => $request->service_qty,
            'payment_type' => 3,
            'first_name' => $request->first_name,
            'user_id' => Auth::user()->id,
            'last_name' => $request->last_name,
            'user_email' => $request->user_email,
            'user_phone' => $request->user_phone,
            'service_offer' => $service_offer,
            'tranaction' => "Bankt",
            'user_address' => $request->user_address,
            'user_city' => $request->user_city,
            'user_state' => $request->user_state,
            'user_postal' => $request->user_postalcode,
            'service_amount' => $request->service_amount,
            'notes' => $request->user_notes,
        ];
        //  print_r($data_insert); exit();
        $bookingId = DB::table('bookings')->insertGetId($data_insert);
        $provider_id = Product::select('created_by')->where('id', $request->product_id)->first();

        try {
            // Call the InvoiceHelper directly
            $pdfPath = \App\Helpers\InvoiceHelper::generateInvoice(
                $bookingId,
                $total_amount,
                '2',
                $provider_id->created_by,
            );
        } catch (\Exception $e) {
            Log::error("Failed to generate invoice: " . $e->getMessage());
        }
        return view('bookingsuccess');
    }

    public function handlecodPayment(Request $request)
    {
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
        $book_details = Bookings::orderBy('id', 'DESC')->first();

        $total_amount = $request->service_amount * $request->service_qty + ($added_amount) + ($request->amount_tax);


        $data_insert = [
            'product_id' => $request->product_id,
            'service_qty' => $request->service_qty,
            'payment_type' => 5,
            'first_name' => $request->first_name,
            'user_id' => Auth::user()->id,
            'last_name' => $request->last_name,
            'user_email' => $request->user_email,
            'user_phone' => $request->user_phone,
            'service_offer' => $service_offer,
            'tranaction' => "cod",
            'total_amount' => $total_amount,
            'user_address' => $request->user_address,
            'user_city' => $request->user_city,
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

        return view('bookingsuccess');
    }

    public function handleWalletPayment(Request $request)
    {
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
        $book_details = Bookings::orderBy('id', 'DESC')->first();

        $total_amount = $request->service_amount * $request->service_qty + ($added_amount) + ($request->amount_tax);


        $data_insert = [
            'product_id' => $request->product_id,
            'service_qty' => $request->service_qty,
            'payment_type' => 6,
            'payment_status' => 2,
            'first_name' => $request->first_name,
            'user_id' => Auth::user()->id,
            'last_name' => $request->last_name,
            'user_email' => $request->user_email,
            'user_phone' => $request->user_phone,
            'service_offer' => $service_offer,
            'tranaction' => "wallet",
            'total_amount' => $total_amount,
            'user_address' => $request->user_address,
            'user_city' => $request->user_city,
            'user_state' => $request->user_state,
            'user_postal' => $request->user_postalcode,
            'service_amount' => $request->service_amount,
            'notes' => $request->user_notes,
        ];

        $bookingId = DB::table('bookings')->insertGetId($data_insert);


        $walletHistory = WalletHistory::create([
            'user_id' => Auth::user()->id,
            'type' => '2',
            'reference_id' => $bookingId,
            'amount' => $total_amount ?? null,
            'payment_type' => 'paypal',
            'transaction_id' => $bookingId,
            'status' => 'completed',
            'transaction_date' => now(),
        ]);

        DB::table('bookings')
            ->where('id', $bookingId)
            ->update(['tranaction' => $walletHistory->id]);

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

        return view('bookingsuccess');
    }

}
