<?php

namespace Modules\PaymentGateway\app\Http\Controllers;

use App\Http\Controllers\BookController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Razorpay\Api\Api;
use App\Models\Bookings;
use App\Models\PackageTrx;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB as FacadesDB;
use Illuminate\Support\Facades\Log;
use Modules\GlobalSetting\Entities\GlobalSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RazerpayController extends Controller
{
    /**
     * Handles the payment initiation for Razorpay.
     *
     * @param Request $request
     * @param string $formattedBookingDate
     * @param int $authId
     * @param string|null $additionalServiceData
     * @param string|null $fromTime
     * @param string|null $toTime
     * @return \Illuminate\Http\JsonResponse
     */
    public function handlePayment(Request $request, $formattedBookingDate, $authId, $additionalServiceData, $fromTime, $toTime)
    {
        try {
            // Validate required input keys
            $requiredFields = [
                'total_amount',
                'service_id',
                'first_name',
                'last_name',
                'email',
                'phone_number',
                'city',
                'state',
                'address',
                'postal',
                'sub_amount',
                'tax_amount'
            ];

            foreach ($requiredFields as $field) {
                if (!$request->filled($field)) {
                    return response()->json([
                        'message' => 'RazorPay is not available at the moment. Please try other payment methods or contact admin.'
                    ], 400);
                }
            }

            // Validate supported currency
            $currencyCode = getDefaultCurrencyCode();

            $supportedCurrencies = [
                'AED',
                'AFN',
                'ALL',
                'AMD',
                'ANG',
                'AOA',
                'ARS',
                'AUD',
                'AWG',
                'AZN',
                'BAM',
                'BBD',
                'BDT',
                'BGN',
                'BHD',
                'BIF',
                'BMD',
                'BND',
                'BOB',
                'BRL',
                'BSD',
                'BTN',
                'BWP',
                'BYN',
                'BZD',
                'CAD',
                'CDF',
                'CHF',
                'CLP',
                'CNY',
                'COP',
                'CRC',
                'CUP',
                'CVE',
                'CZK',
                'DJF',
                'DKK',
                'DOP',
                'DZD',
                'EGP',
                'ERN',
                'ETB',
                'EUR',
                'FJD',
                'FKP',
                'FOK',
                'GBP',
                'GEL',
                'GGP',
                'GHS',
                'GIP',
                'GMD',
                'GNF',
                'GTQ',
                'GYD',
                'HKD',
                'HNL',
                'HRK',
                'HTG',
                'HUF',
                'IDR',
                'ILS',
                'IMP',
                'INR',
                'IQD',
                'IRR',
                'ISK',
                'JEP',
                'JMD',
                'JOD',
                'JPY',
                'KES',
                'KGS',
                'KHR',
                'KID',
                'KMF',
                'KRW',
                'KWD',
                'KYD',
                'KZT',
                'LAK',
                'LBP',
                'LKR',
                'LRD',
                'LSL',
                'LYD',
                'MAD',
                'MDL',
                'MGA',
                'MKD',
                'MMK',
                'MNT',
                'MOP',
                'MRU',
                'MUR',
                'MVR',
                'MWK',
                'MXN',
                'MYR',
                'MZN',
                'NAD',
                'NGN',
                'NIO',
                'NOK',
                'NPR',
                'NZD',
                'OMR',
                'PAB',
                'PEN',
                'PGK',
                'PHP',
                'PKR',
                'PLN',
                'PYG',
                'QAR',
                'RON',
                'RSD',
                'RUB',
                'RWF',
                'SAR',
                'SBD',
                'SCR',
                'SDG',
                'SEK',
                'SGD',
                'SHP',
                'SLE',
                'SOS',
                'SRD',
                'SSP',
                'STN',
                'SYP',
                'SZL',
                'THB',
                'TJS',
                'TMT',
                'TND',
                'TOP',
                'TRY',
                'TTD',
                'TVD',
                'TWD',
                'TZS',
                'UAH',
                'UGX',
                'USD',
                'UYU',
                'UZS',
                'VES',
                'VND',
                'VUV',
                'WST',
                'XAF',
                'XCD',
                'XOF',
                'XPF',
                'YER',
                'ZAR',
                'ZMW',
                'ZWL'
            ];

            if (!in_array(strtoupper($currencyCode), $supportedCurrencies)) {
                return response()->json([
                    'message' => 'Currency not supported. Please try a different payment method.'
                ], 400);
            }

            // Initialize Razorpay
            $api = new Api(config('razorpay.key'), config('razorpay.secret'));

            $razorpayOrder = $api->order->create([
                'receipt'  => 'order_rcptid_' . time(),
                'amount'   => intval($request->total_amount * 100),
                'currency' => $currencyCode,
                'notes'    => [
                    'source'  => 'booking',
                    'user_id' => $authId ?? 0,
                ],
            ]);

            // Booking data...
            $data = [
                "product_id"     => $request->input('service_id'),
                "branch_id"      => $request->input('branch_id') ?? 0,
                "staff_id"       => $request->input('staff_id') ?? 0,
                "slot_id"        => $request->input('slot_id') ?? 0,
                "booking_date"   => $formattedBookingDate,
                "from_time"      => $request->input('from_time') ?? $fromTime,
                "to_time"        => $request->input('to_time') ?? $toTime,
                "booking_status" => 1,
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
                'tranaction'     => $razorpayOrder['id'],
                "payment_type"   => 3,
                "payment_status" => 1,
                "service_qty"    => 1,
                "service_amount" => $request->input('sub_amount'),
                "total_amount"   => $request->input('total_amount'),
            ];

            if ($additionalServiceData) {
                $data['additional_services'] = $additionalServiceData;
            }

            $save = Bookings::create($data);

            $orderId = getBookingOrderId($save->id);
            $save->update(['order_id' => $orderId]);

            if ($save && $request->filled('coupon_id')) {
                FacadesDB::table('coupon_logs')->insert([
                    'user_id'      => $save->user_id,
                    'booking_id'   => $save->id,
                    'coupon_id'    => $request->input('coupon_id'),
                    'coupon_code'  => $request->input('coupon_code'),
                    'coupon_value' => $request->input('coupon_value'),
                    'created_at'   => now(),
                    'updated_at'   => now()
                ]);
            }

            return response()->json([
                'message'  => 'Order created successfully.',
                'razorpay' => [
                    'key'        => config('razorpay.key'),
                    'order_id'   => $razorpayOrder['id'],
                    'amount'     => intval($request->total_amount * 100),
                    'currency'   => $currencyCode,
                    'booking_id' => $save->id,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Razorpay process failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'RazorPay is not available at the moment. Please try other payment methods or contact admin.'
            ], 500);
        }
    }

    /**
     * Handle the successful payment callback from Razorpay.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function razorpaySuccess(Request $request)
    {
        $request->validate([
            'razorpay_order_id'   => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature'  => 'required|string',
            'booking_id'          => 'required|integer|exists:bookings,id',
        ]);

        try {
            $api = new Api(config('razorpay.key'), config('razorpay.secret'));

            $attributes = [
                'razorpay_order_id'   => $request->razorpay_order_id,
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature'  => $request->razorpay_signature,
            ];

            $api->utility->verifyPaymentSignature($attributes);

            Bookings::where('id', $request->booking_id)
                ->where('tranaction', $request->razorpay_order_id)
                ->update(['payment_status' => 2]);

            $booking = Bookings::where('tranaction', $request->razorpay_order_id)->first();
            sendBookingNotification($booking->id ?? null);

            return redirect()->route('payment.two');
        } catch (\Exception $e) {
            Log::error('Razorpay payment verification failed: ' . $e->getMessage());
            Bookings::where('id', $request->booking_id)->update(['booking_status' => 3]); // 3 = Failed
            return redirect()->route('payment.failed_page')->with('error', 'Payment verification failed.');
        }
    }

    public function razorpaySubscription(Request $request)
    {
        try {
            $authId = Auth::id();
            $currencyCode = strtoupper(getDefaultCurrencyCode());

            $supportedCurrencies = [
                'AED',
                'AFN',
                'ALL',
                'AMD',
                'ANG',
                'AOA',
                'ARS',
                'AUD',
                'AWG',
                'AZN',
                'BAM',
                'BBD',
                'BDT',
                'BGN',
                'BHD',
                'BIF',
                'BMD',
                'BND',
                'BOB',
                'BRL',
                'BSD',
                'BTN',
                'BWP',
                'BYN',
                'BZD',
                'CAD',
                'CDF',
                'CHF',
                'CLP',
                'CNY',
                'COP',
                'CRC',
                'CUP',
                'CVE',
                'CZK',
                'DJF',
                'DKK',
                'DOP',
                'DZD',
                'EGP',
                'ERN',
                'ETB',
                'EUR',
                'FJD',
                'FKP',
                'FOK',
                'GBP',
                'GEL',
                'GGP',
                'GHS',
                'GIP',
                'GMD',
                'GNF',
                'GTQ',
                'GYD',
                'HKD',
                'HNL',
                'HRK',
                'HTG',
                'HUF',
                'IDR',
                'ILS',
                'IMP',
                'INR',
                'IQD',
                'IRR',
                'ISK',
                'JEP',
                'JMD',
                'JOD',
                'JPY',
                'KES',
                'KGS',
                'KHR',
                'KID',
                'KMF',
                'KRW',
                'KWD',
                'KYD',
                'KZT',
                'LAK',
                'LBP',
                'LKR',
                'LRD',
                'LSL',
                'LYD',
                'MAD',
                'MDL',
                'MGA',
                'MKD',
                'MMK',
                'MNT',
                'MOP',
                'MRU',
                'MUR',
                'MVR',
                'MWK',
                'MXN',
                'MYR',
                'MZN',
                'NAD',
                'NGN',
                'NIO',
                'NOK',
                'NPR',
                'NZD',
                'OMR',
                'PAB',
                'PEN',
                'PGK',
                'PHP',
                'PKR',
                'PLN',
                'PYG',
                'QAR',
                'RON',
                'RSD',
                'RUB',
                'RWF',
                'SAR',
                'SBD',
                'SCR',
                'SDG',
                'SEK',
                'SGD',
                'SHP',
                'SLE',
                'SOS',
                'SRD',
                'SSP',
                'STN',
                'SYP',
                'SZL',
                'THB',
                'TJS',
                'TMT',
                'TND',
                'TOP',
                'TRY',
                'TTD',
                'TVD',
                'TWD',
                'TZS',
                'UAH',
                'UGX',
                'USD',
                'UYU',
                'UZS',
                'VES',
                'VND',
                'VUV',
                'WST',
                'XAF',
                'XCD',
                'XOF',
                'XPF',
                'YER',
                'ZAR',
                'ZMW',
                'ZWL'
            ];

            if (!in_array($currencyCode, $supportedCurrencies)) {
                return response()->json([
                    'message' => 'Currency not supported for Razorpay. Please use a different payment method.'
                ], 400);
            }

            $api = new Api(config('razorpay.key'), config('razorpay.secret'));

            $razorpayOrder = $api->order->create([
                'receipt'  => 'order_rcptid_' . time(),
                'amount'   => intval($request->service_amount * 100),
                'currency' => $currencyCode,
                'notes'    => [
                    'source'  => 'booking',
                    'user_id' => $authId ?? 0,
                ],
            ]);

            PackageTrx::where('id', $request->trx_id)->update([
                'transaction_id' => $razorpayOrder['id']
            ]);

            return response()->json([
                'message' => 'Order created successfully.',
                'razorpay' => [
                    'key'      => config('razorpay.key'),
                    'order_id' => $razorpayOrder['id'],
                    'amount'   => intval($request->service_amount * 100),
                    'currency' => $currencyCode,
                ]
            ]);
        } catch (\Throwable $e) {
            Log::error('Razorpay Subscription Error: ' . $e->getMessage());

            return response()->json([
                'message' => 'Unable to initiate payment via Razorpay at the moment. Please try again later.',
                'error'   => app()->environment('production') ? null : $e->getMessage(), // hide error in production
            ], 500);
        }
    }

    public function razorpaySubscriptionSuccess(Request $request)
    {
        $request->validate([
            'razorpay_order_id'   => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature'  => 'required|string',
        ]);

        $api = new Api(config('razorpay.key'), config('razorpay.secret'));

        $attributes = [
            'razorpay_order_id'   => $request->razorpay_order_id,
            'razorpay_payment_id' => $request->razorpay_payment_id,
            'razorpay_signature'  => $request->razorpay_signature,
        ];

        $api->utility->verifyPaymentSignature($attributes);

        PackageTrx::where('transaction_id', $request->razorpay_order_id)->update(['payment_status' => 2, 'status' => 1]);

        return view('provider.subscription.payment_success');
    }

    public function storeRazorpay(Request $request)
    {
        $request->validate([
            'razorpay_key'    => 'required|string',
            'razorpay_secret' => 'required|string',
            'razorpay_mode'   => 'required|in:test,live',
        ]);

        try {
            // Determine mode
            $inputMode = $request->razorpay_mode; // test or live
            $envMode   = $inputMode === 'test' ? 'sandbox' : 'production';

            // Prepare env updates
            $envUpdates = [
                'RAZORPAY_KEY'    => $request->razorpay_key,
                'RAZORPAY_SECRET' => $request->razorpay_secret,
                'RAZORPAY_MODE'   => $envMode,
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

            // Step 2: Save to database
            $dbUpdates = [
                'razorpay_key'    => $request->razorpay_key,
                'razorpay_secret' => $request->razorpay_secret,
                'razorpay_mode'   => $inputMode, // store test/live in DB
            ];

            foreach ($dbUpdates as $key => $value) {
                DB::table('general_settings')->updateOrInsert(
                    [
                        'key'      => $key,
                        'group_id' => 13,
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
                'message' => 'Error! updating Razorpay settings',
                'error'   => $e->getTraceAsString(),
            ], 500);
        }
    }

    public function statusRazorpay(Request $request)
    {
        DB::table('general_settings')->updateOrInsert(
            [
                'key' => 'razorpay_status',
                'group_id' => 13,
            ],
            [
                'value' => $request->razorpay_status,
                'updated_at' => now(),
            ]
        );

        DB::table('payment_methods')->updateOrInsert(
            [
                'payment_type' => 'RazerPay',
            ],
            [
                'label'      => 'razerpay',
                'status'     => $request->razorpay_status,
                'created_by' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $message = $request->razorpay_status == 1
            ? 'Razorpay status enabled'
            : 'Razorpay status disabled';

        return response()->json([
            'code' => 200,
            'message' => $message,
        ]);
    }

    //_____________________________________________________________________________________________________________________________________________________________________

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

        try {
            // --- 1. Validation (largely the same as your web function) ---
            $requiredFields = [
                'total_amount',
                'service_id',
                'first_name',
                'last_name',
                'email',
                'phone_number',
                'city',
                'state',
                'address',
                'postal',
                'sub_amount',
                'tax_amount',
                'booking_date',
                'from_time',
                'to_time'
            ];

            foreach ($requiredFields as $field) {
                if (!$request->filled($field)) {
                    // Provide a more specific error message for the mobile team
                    return response()->json(['message' => "The field '{$field}' is required."], 400);
                }
            }

            // Currency validation
            $currencyCode = getDefaultCurrencyCode(); // Assuming this helper function exists
            // A smaller list for example, use your full list from the web function
            $supportedCurrencies = [
                'AED',
                'AFN',
                'ALL',
                'AMD',
                'ANG',
                'AOA',
                'ARS',
                'AUD',
                'AWG',
                'AZN',
                'BAM',
                'BBD',
                'BDT',
                'BGN',
                'BHD',
                'BIF',
                'BMD',
                'BND',
                'BOB',
                'BRL',
                'BSD',
                'BTN',
                'BWP',
                'BYN',
                'BZD',
                'CAD',
                'CDF',
                'CHF',
                'CLP',
                'CNY',
                'COP',
                'CRC',
                'CUP',
                'CVE',
                'CZK',
                'DJF',
                'DKK',
                'DOP',
                'DZD',
                'EGP',
                'ERN',
                'ETB',
                'EUR',
                'FJD',
                'FKP',
                'FOK',
                'GBP',
                'GEL',
                'GGP',
                'GHS',
                'GIP',
                'GMD',
                'GNF',
                'GTQ',
                'GYD',
                'HKD',
                'HNL',
                'HRK',
                'HTG',
                'HUF',
                'IDR',
                'ILS',
                'IMP',
                'INR',
                'IQD',
                'IRR',
                'ISK',
                'JEP',
                'JMD',
                'JOD',
                'JPY',
                'KES',
                'KGS',
                'KHR',
                'KID',
                'KMF',
                'KRW',
                'KWD',
                'KYD',
                'KZT',
                'LAK',
                'LBP',
                'LKR',
                'LRD',
                'LSL',
                'LYD',
                'MAD',
                'MDL',
                'MGA',
                'MKD',
                'MMK',
                'MNT',
                'MOP',
                'MRU',
                'MUR',
                'MVR',
                'MWK',
                'MXN',
                'MYR',
                'MZN',
                'NAD',
                'NGN',
                'NIO',
                'NOK',
                'NPR',
                'NZD',
                'OMR',
                'PAB',
                'PEN',
                'PGK',
                'PHP',
                'PKR',
                'PLN',
                'PYG',
                'QAR',
                'RON',
                'RSD',
                'RUB',
                'RWF',
                'SAR',
                'SBD',
                'SCR',
                'SDG',
                'SEK',
                'SGD',
                'SHP',
                'SLE',
                'SOS',
                'SRD',
                'SSP',
                'STN',
                'SYP',
                'SZL',
                'THB',
                'TJS',
                'TMT',
                'TND',
                'TOP',
                'TRY',
                'TTD',
                'TVD',
                'TWD',
                'TZS',
                'UAH',
                'UGX',
                'USD',
                'UYU',
                'UZS',
                'VES',
                'VND',
                'VUV',
                'WST',
                'XAF',
                'XCD',
                'XOF',
                'XPF',
                'YER',
                'ZAR',
                'ZMW',
                'ZWL'
            ];
            if (!in_array(strtoupper($currencyCode), $supportedCurrencies)) {
                return response()->json([
                    'message' => 'Currency not supported.'
                ], 400);
            }

            // --- 2. Create Razorpay Order ---
            $api = new Api(config('razorpay.key'), config('razorpay.secret'));
            $razorpayOrder = $api->order->create([
                'receipt'  => 'order_rcptid_' . time(),
                'amount'   => intval($request->total_amount * 100), // amount in the smallest currency unit
                'currency' => $currencyCode,
                'notes'    => [
                    'source'  => 'booking_api',
                    'user_id' => $request->user_id,
                ],
            ]);

            // --- 3. Create Booking Record in your Database ---
            $data = [
                "product_id"       => $request->input('service_id'),
                "branch_id"        => $request->input('branch_id') ?? 0,
                "staff_id"         => $request->input('staff_id') ?? 0,
                "slot_id"          => $request->input('slot_id') ?? 0,
                "booking_date" => $bookingDate,
                "from_time"        => $request->input('from_time'),
                "to_time"          => $request->input('to_time'),
                "booking_status"   => 1, // 1 = Pending
                "amount_tax"       => $request->input('tax_amount'),
                "user_id"          => $request->user_id,
                "first_name"       => $request->input('first_name'),
                "last_name"        => $request->input('last_name'),
                "user_email"       => $request->input('email'),
                "user_phone"       => $request->input('phone_number'),
                "user_city"        => $request->input('city'),
                "user_state"       => $request->input('state'),
                "user_address"     => $request->input('address'),
                "notes"             => $request->input('note'),
                "user_postal"      => $request->input('postal'),
                'tranaction'       => $razorpayOrder['id'], // Store Razorpay Order ID
                "payment_type"     => 3, // 3 = Razorpay
                "payment_status"   => 1, // 1 = Pending
                "service_qty"      => 1,
                "service_amount"   => $request->input('sub_amount'),
                "total_amount"     => $request->input('total_amount'),
            ];

            if ($request->has('additional_services')) {
                $data['additional_services'] = $request->input('additional_services');
            }

            $booking = Bookings::create($data);

            if ($booking) {
                $orderId = getBookingOrderId($booking->id);
                $booking->update(['order_id' => $orderId]);
            }

            if ($request->filled('coupon_id')) {
                FacadesDB::table('coupon_logs')->insert([
                    'user_id'      => $booking->user_id,
                    'booking_id'   => $booking->id,
                    'coupon_id'    => $request->input('coupon_id'),
                    'coupon_code'  => $request->input('coupon_code'),
                    'coupon_value' => $request->input('coupon_value'),
                    'created_at'   => now(),
                    'updated_at'   => now()
                ]);
            }

            // --- 4. Return Response to Mobile App ---
            return response()->json([
                'message'  => 'Order created successfully. Proceed to payment.',
                'data'     => [
                    'key'        => config('razorpay.key'),
                    'order_id'   => $razorpayOrder['id'],
                    'amount'     => intval($request->total_amount * 100),
                    'currency'   => $currencyCode,
                    'booking_id' => $booking->id, // Your internal booking ID
                    'prefill'    => [
                        'name'    => $request->input('first_name') . ' ' . $request->input('last_name'),
                        'email'   => $request->input('email'),
                        'contact' => $request->input('phone_number'),
                    ],
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Razorpay API process failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'Payment gateway is currently unavailable. Please try again later.'
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
            'razorpay_order_id'   => 'required|string',
            'booking_id'          => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid data provided', 'errors' => $validator->errors()], 422);
        }

        try {
            $booking = Bookings::where('id', $request->booking_id)
                ->where('tranaction', $request->razorpay_order_id)
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
