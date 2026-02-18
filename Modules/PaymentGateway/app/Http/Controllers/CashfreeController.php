<?php

namespace Modules\PaymentGateway\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Bookings;
use App\Models\PackageTrx;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Modules\GlobalSetting\Entities\GlobalSetting;
use Illuminate\Support\Str; // Make sure this line is at the top of your file
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class CashfreeController extends Controller
{
    // The API version for Cashfree PG
    private $x_api_version = "2023-08-01";

    /**
     * Create a Cashfree order and return a redirect URL.
     */
    public function handlePayment(Request $request, $formattedBookingDate, $authId, $additionalServiceData, $fromTime, $toTime)
    {
        try {
            // Step 0: Validate required fields
            $requiredFields = [
                'service_id',
                'tax_amount',
                'first_name',
                'last_name',
                'email',
                'phone_number',
                'city',
                'state',
                'address',
                'postal',
                'sub_amount',
                'total_amount'
            ];

            foreach ($requiredFields as $field) {
                if (!$request->filled($field)) {
                    return response()->json([
                        'message' => 'Cashfree is not available at the moment. Please try other payment methods or contact admin.'
                    ], 400);
                }
            }

            // Step 1: Validate supported currency
            $currencyCode = getDefaultCurrencyCode();
            $supportedCurrencies = [
                'AUD',
                'BRL',
                'CAD',
                'CHF',
                'CNY',
                'CZK',
                'EUR',
                'GBP',
                'HKD',
                'HUF',
                'ILS',
                'INR',
                'JPY',
                'MXN',
                'MYR',
                'NOK',
                'NZD',
                'PHP',
                'PLN',
                'SEK',
                'SGD',
                'THB',
                'TWD',
                'USD'
            ];

            if (!in_array(strtoupper($currencyCode), $supportedCurrencies)) {
                return response()->json([
                    'message' => 'Currency not supported. Please try a different payment method.'
                ], 400);
            }

            // Step 2: Create a pending booking record in your database
            $orderId = 'order_' . rand(1111111111, 9999999999);

            $bookingData = [
                "product_id"     => $request->input('service_id'),
                "branch_id"      => $request->input('branch_id') ?? 0,
                "staff_id"       => $request->input('staff_id') ?? 0,
                "slot_id"        => $request->input('slot_id') ?? 0,
                "booking_date"   => $formattedBookingDate,
                "from_time"      => $request->input('from_time') ?? $fromTime,
                "to_time"        => $request->input('to_time') ?? $toTime,
                "booking_status" => 1,
                "payment_status" => 1,
                "amount_tax"     => $request->input('tax_amount'),
                "user_id"        => $authId,
                "first_name"     => $request->input('first_name'),
                "last_name"      => $request->input('last_name'),
                "user_email"     => $request->input('email'),
                "user_phone"     => $request->input('phone_number'),
                "user_city"      => $request->input('city'),
                "user_state"     => $request->input('state'),
                "user_address"   => $request->input('address'),
                "notes"           => $request->input('note'),
                "user_postal"    => $request->input('postal'),
                "tranaction"     => $orderId,
                "payment_type"   => 9, // Cashfree
                "service_qty"    => 1,
                "service_amount" => $request->input('sub_amount'),
                "total_amount"   => $request->input('total_amount'),
            ];

            if ($additionalServiceData) {
                $bookingData['additional_services'] = $additionalServiceData;
            }

            $booking = Bookings::create($bookingData);

            if ($booking) {
                $bookingOrderId = getBookingOrderId($booking->id);
                $booking->update(['order_id' => $bookingOrderId]);
            }

            $url = "https://sandbox.cashfree.com/pg/orders";

            $headers = [
                "Content-Type: application/json",
                "x-api-version: 2022-01-01",
                "x-client-id: " . env('CASHFREE_API_KEY'),
                "x-client-secret: " . env('CASHFREE_API_SECRET')
            ];

            $payload = [
                'order_id'        => $orderId,
                'order_amount'    => $request->input('total_amount'),
                "order_currency"  => $currencyCode,
                "customer_details" => [
                    "customer_id"    => 'customer_' . rand(111111111, 999999999),
                    "customer_name"  => $request->input('first_name'),
                    "customer_email" => $request->input('email'),
                    "customer_phone" => $request->input('phone_number'),
                ],

                'order_meta' => [
                    'return_url' => url('/cashfree/payments/success') . '/?order_id={order_id}&order_token={order_token}'
                ]
            ];

            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($payload));

            $resp = curl_exec($curl);
            curl_close($curl);

            $responseData = json_decode($resp, true);

            if (isset($responseData['payment_link'])) {
                return response()->json([
                    'cashfree' => [
                        'redirect_url' => $responseData['payment_link']
                    ]
                ]);
            } else {
                Log::error('Cashfree order creation failed: ' . $resp);
                return response()->json([
                    'message' => 'Cashfree is not available at the moment. Please try other payment methods or contact admin.'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Cashfree payment process failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'Cashfree is not available at the moment. Please try other payment methods or contact admin.'
            ], 500);
        }
    }

    /**
     * Handle the callback after payment from Cashfree.
     */
    public function success(Request $request)
    {
        Bookings::where('tranaction', $request->order_id)
            ->update(['payment_status' => 2]);

        $booking = Bookings::where('tranaction', $request->order_id)->first();
        sendBookingNotification($booking->id ?? null);

        return redirect()->route('payment.two');
    }

    public function cashfreeSubscription(Request $request)
    {
        try {
            $currencyCode = strtoupper(getDefaultCurrencyCode());
            $authId = Auth::id();
            $user = User::find($authId);

            if (!$user) {
                return response()->json([
                    'message' => 'User not found.'
                ], 404);
            }

            $supportedCurrencies = [
                'AUD',
                'BRL',
                'CAD',
                'CHF',
                'CNY',
                'CZK',
                'EUR',
                'GBP',
                'HKD',
                'HUF',
                'ILS',
                'INR',
                'JPY',
                'MXN',
                'MYR',
                'NOK',
                'NZD',
                'PHP',
                'PLN',
                'SEK',
                'SGD',
                'THB',
                'TWD',
                'USD'
            ];

            if (!in_array($currencyCode, $supportedCurrencies)) {
                return response()->json([
                    'message' => 'Currency not supported. Please try a different payment method.'
                ], 400);
            }

            $orderId = 'order_' . rand(1111111111, 9999999999);

            $url = "https://sandbox.cashfree.com/pg/orders";

            $headers = [
                "Content-Type: application/json",
                "x-api-version: 2022-01-01",
                "x-client-id: " . env('CASHFREE_API_KEY'),
                "x-client-secret: " . env('CASHFREE_API_SECRET')
            ];

            $payload = [
                'order_id'         => $orderId,
                'order_amount'     => $request->input('service_amount'),
                'order_currency'   => $currencyCode,
                'customer_details' => [
                    'customer_id'    => 'customer_' . rand(111111111, 999999999),
                    'customer_name'  => $user->name,
                    'customer_email' => $user->email,
                    'customer_phone' => $user->phone_number,
                ],
                'order_meta' => [
                    'return_url' => url('/cashfree/sub/success') . '/?order_id={order_id}&order_token={order_token}'
                ]
            ];

            $curl = curl_init($url);
            curl_setopt_array($curl, [
                CURLOPT_POST           => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER     => $headers,
                CURLOPT_POSTFIELDS     => json_encode($payload),
            ]);

            $resp = curl_exec($curl);
            curl_close($curl);

            $responseData = json_decode($resp, true);

            PackageTrx::where('id', $request->trx_id)->update([
                'transaction_id' => $orderId
            ]);

            if (isset($responseData['payment_link'])) {
                return response()->json([
                    'cashfree' => [
                        'redirect_url' => $responseData['payment_link']
                    ]
                ]);
            } else {
                Log::error('Cashfree order creation failed: ' . $resp);
                return response()->json([
                    'message' => 'Cashfree is not available at the moment. Please try other payment methods or contact admin.'
                ], 500);
            }
        } catch (\Throwable $e) {
            Log::error('Cashfree Subscription Error: ' . $e->getMessage());

            return response()->json([
                'message' => 'Unable to initiate payment via Cashfree at the moment. Please try again later.',
                'error'   => app()->environment('production') ? null : $e->getMessage(),
            ], 500);
        }
    }

    public function cashfreeSubscriptionSuccess(Request $request)
    {
        PackageTrx::where('transaction_id', $request->order_id)->update(['payment_status' => 2, 'status' => 1]);

        return view('provider.subscription.payment_success');
    }

    public function storeCashfree(Request $request)
    {
        $request->validate([
            'cashfree_api_key'    => 'required|string',
            'cashfree_api_secret' => 'required|string',
            'cashfree_mode'       => 'required|in:test,live',
        ]);

        try {
            // Determine mode for .env file (sandbox/production)
            $inputMode = $request->cashfree_mode; // 'test' or 'live' from form
            $envMode   = $inputMode === 'test' ? 'sandbox' : 'production';

            // Prepare env updates
            $envUpdates = [
                'CASHFREE_API_KEY'    => $request->cashfree_api_key,
                'CASHFREE_API_SECRET' => $request->cashfree_api_secret,
                'CASHFREE_MODE'       => $envMode,
            ];

            // Step 1: Update .env file
            $envPath = base_path('.env');
            if (file_exists($envPath)) {
                $envContent = file_get_contents($envPath);

                foreach ($envUpdates as $key => $value) {
                    $pattern = "/^{$key}=.*$/m";
                    $line = "{$key}=\"{$value}\"";

                    if (preg_match($pattern, $envContent)) {
                        $envContent = preg_replace($pattern, $line, $envContent);
                    } else {
                        $envContent .= PHP_EOL . $line;
                    }
                }
                file_put_contents($envPath, $envContent);
            }

            // Step 2: Save to database (storing 'test'/'live' for UI consistency)
            $dbUpdates = [
                'cashfree_api_key'    => $request->cashfree_api_key,
                'cashfree_api_secret' => $request->cashfree_api_secret,
                'cashfree_mode'       => $inputMode, // Storing 'test' or 'live'
            ];

            foreach ($dbUpdates as $key => $value) {
                DB::table('general_settings')->updateOrInsert(
                    [
                        'key'      => $key,
                        'group_id' => 13, // Assuming group_id 4 is for payment credentials
                    ],
                    [
                        'value'      => $value,
                        'updated_at' => now(),
                    ]
                );
            }

            return response()->json([
                'code'    => 200,
                'message' => __('credential_settings_update_success'),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code'    => 500,
                'message' => 'Error! updating Cashfree settings: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the status of the Cashfree payment gateway.
     */
    public function statusCashfree(Request $request)
    {
        DB::table('general_settings')->updateOrInsert(
            [
                'key' => 'cashfree_status',
                'group_id' => 13, // Assuming group_id 4 is for payment credentials
            ],
            [
                'value' => $request->cashfree_status,
                'updated_at' => now(),
            ]
        );

        DB::table('payment_methods')->updateOrInsert(
            [
                'payment_type' => 'Cashfree',
            ],
            [
                'label'      => 'cashfree',
                'status'     => $request->cashfree_status,
                'created_by' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $message = $request->cashfree_status == 1
            ? 'Cashfree status enabled'
            : 'Cashfree status disabled';

        return response()->json([
            'code' => 200,
            'message' => $message,
        ]);
    }
    //Mobile Responce API

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function initiatePayment(Request $request)
    {
        $bookingDate = null;
        if ($request->filled('booking_date')) {
            $bookingDate = Carbon::parse($request->input('booking_date'))->format('Y-m-d');
        }

        // Required fields check
        $requiredFields = [
            'service_id',
            'tax_amount',
            'first_name',
            'last_name',
            'email',
            'phone_number',
            'city',
            'state',
            'address',
            'postal',
            'sub_amount',
            'total_amount'
        ];

        foreach ($requiredFields as $field) {
            if (!$request->filled($field)) {
                return response()->json([
                    'message' => "Missing required field: {$field}",
                    'error'   => 'Cashfree is not available at the moment. Please try other payment methods or contact admin.'
                ], 400);
            }
        }

        $currencyCode = getDefaultCurrencyCode();

        $supportedCurrencies = [
            'AUD',
            'BRL',
            'CAD',
            'CHF',
            'CNY',
            'CZK',
            'EUR',
            'GBP',
            'HKD',
            'HUF',
            'ILS',
            'INR',
            'JPY',
            'MXN',
            'MYR',
            'NOK',
            'NZD',
            'PHP',
            'PLN',
            'SEK',
            'SGD',
            'THB',
            'TWD',
            'USD'
        ];

        if (!in_array(strtoupper($currencyCode), $supportedCurrencies)) {
            return response()->json([
                'message' => "Currency '{$currencyCode}' not supported by Cashfree.",
                'error'   => 'Cashfree is not available at the moment. Please try other payment methods or contact admin.'
            ], 400);
        }

        $orderId = 'order_' . rand(1111111111, 9999999999);

        $booking = Bookings::create([
            "product_id"     => $request->service_id,
            "branch_id"      => $request->branch_id ?? 0,
            "staff_id"       => $request->staff_id ?? 0,
            "slot_id"        => $request->slot_id ?? 0,
            "booking_date"   => $bookingDate,
            "from_time"      => $request->from_time,
            "to_time"        => $request->to_time,
            "booking_status" => 1,
            "payment_status" => 1,
            "amount_tax"     => $request->tax_amount,
            "user_id"        => $request->user_id,
            "first_name"     => $request->first_name,
            "last_name"      => $request->last_name,
            "user_email"     => $request->email,
            "user_phone"     => $request->phone_number,
            "user_city"      => $request->city,
            "user_state"     => $request->state,
            "user_address"   => $request->address,
            "notes"          => $request->note,
            "user_postal"    => $request->postal,
            "tranaction"     => $orderId,
            "payment_type"   => 9, // Cashfree
            "service_qty"    => 1,
            "service_amount" => $request->sub_amount,
            "total_amount"   => $request->total_amount,
        ]);

        if ($booking) {
            $bookingOrderId = getBookingOrderId($booking->id);
            $booking->update(['order_id' => $bookingOrderId]);
        }

        $url = "https://sandbox.cashfree.com/pg/orders";

        $headers = [
            "Content-Type: application/json",
            "x-api-version: 2022-01-01",
            "x-client-id: " . env('CASHFREE_API_KEY'),
            "x-client-secret: " . env('CASHFREE_API_SECRET')
        ];

        $payload = [
            'order_id'       => $orderId,
            'order_amount'   => $request->input('total_amount'),
            "order_currency" => $currencyCode,
            "customer_details" => [
                "customer_id"    => 'customer_' . rand(111111111, 999999999),
                "customer_name"  => $request->input('first_name'),
                "customer_email" => $request->input('email'),
                "customer_phone" => $request->input('phone_number'),
            ],
        ];

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($payload));

        $resp = curl_exec($curl);

        if ($resp === false) {
            $errorMsg = curl_error($curl);
            curl_close($curl);

            Log::error('Cashfree cURL Error: ' . $errorMsg);

            return response()->json([
                'message' => "cURL error occurred: {$errorMsg}",
                'error'   => 'Cashfree is not available at the moment. Please try other payment methods or contact admin.'
            ], 500);
        }

        curl_close($curl);

        $cashfreeOrder = json_decode($resp, true);

        if (
            isset($cashfreeOrder['order_status']) &&
            $cashfreeOrder['order_status'] === 'ACTIVE' &&
            (!empty($cashfreeOrder['payment_session_id']) || !empty($cashfreeOrder['order_token']))
        ) {
            return response()->json([
                'booking_id'       => $booking->id,
                'order_amount'     => $request->input('total_amount'),
                'orderId'          => $orderId,
                'paymentSessionId' => $cashfreeOrder['payment_session_id'] ?? null,
                'orderToken'       => $cashfreeOrder['order_token'] ?? null,
            ]);
        } else {
            Log::error('Cashfree order creation failed', $cashfreeOrder);

            return response()->json([
                'message' => 'Cashfree order creation failed.',
                'error'   => $cashfreeOrder['message'] ?? 'Unknown error occurred while creating order with Cashfree.',
                'details' => $cashfreeOrder
            ], 500);
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id'   => 'required|string',
            'booking_id' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid data provided', 'errors' => $validator->errors()], 422);
        }

        $orderId = $request->input('order_id');

        try {
            $booking = Bookings::where('tranaction', $orderId)
                ->firstOrFail();

            sendBookingNotification($booking->id ?? null);

            $booking->update([
                'payment_status' => 2,
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Payment successful and booking confirmed.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Payment verification failed. Please contact support.'
            ], 400);
        }
    }
}
