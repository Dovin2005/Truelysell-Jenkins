<?php

namespace Modules\PaymentGateway\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bookings;
use App\Models\PackageTrx;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB as FacadesDB;
use Illuminate\Support\Facades\Log;
use Modules\GlobalSetting\Entities\GlobalSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PayUController extends Controller
{
    /**
     * Handles the payment initiation for PayU.
     * This version includes the definitive fix for the hash calculation.
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
            $validator = Validator::make($request->all(), [
                'service_id'   => 'required|integer',
                'tax_amount'   => 'required|numeric|min:0',
                'sub_amount'   => 'required|numeric|min:0',
                'total_amount' => 'required|numeric|min:0',
                'first_name'   => 'required|string|max:255',
                'last_name'    => 'nullable|string|max:255',
                'email'        => 'required|email',
                'phone_number' => 'required|string|max:20',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'PayU is not available at the moment. Please try other payment methods or contact admin.',
                    'errors' => $validator->errors()
                ], 422);
            }

            $currencyCode = getDefaultCurrencyCode();
            $allowedCurrencies = [
                'AED',
                'AFN',
                'ALL',
                'AMD',
                'ARS',
                'AUD',
                'AWG',
                'AZN',
                'BAM',
                'BDT',
                'BGN',
                'BHD',
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
                'CHF',
                'CLP',
                'CNY',
                'COP',
                'CRC',
                'CZK',
                'DKK',
                'DOP',
                'DZD',
                'EGP',
                'ETB',
                'EUR',
                'FJD',
                'GBP',
                'GHS',
                'GTQ',
                'HKD',
                'HNL',
                'HRK',
                'HTG',
                'HUF',
                'IDR',
                'ILS',
                'INR',
                'IQD',
                'IRR',
                'ISK',
                'JMD',
                'JOD',
                'JPY',
                'KES',
                'KHR',
                'KMF',
                'KRW',
                'KWD',
                'KZT',
                'LAK',
                'LBP',
                'LKR',
                'LYD',
                'MAD',
                'MDL',
                'MKD',
                'MMK',
                'MNT',
                'MOP',
                'MUR',
                'MVR',
                'MWK',
                'MXN',
                'MYR',
                'NAD',
                'NGN',
                'NIO',
                'NOK',
                'NPR',
                'NZD',
                'OMR',
                'PEN',
                'PGK',
                'PHP',
                'PKR',
                'PLN',
                'QAR',
                'RON',
                'RSD',
                'RUB',
                'SAR',
                'SCR',
                'SEK',
                'SGD',
                'SLL',
                'SOS',
                'SRD',
                'SSP',
                'SZL',
                'THB',
                'TND',
                'TOP',
                'TRY',
                'TTD',
                'TWD',
                'TZS',
                'UAH',
                'UGX',
                'USD',
                'UYU',
                'UZS',
                'VND',
                'XAF',
                'XCD',
                'XOF',
                'YER',
                'ZAR',
                'ZMW',
                'ZWL'
            ];

            if (!in_array(strtoupper($currencyCode), $allowedCurrencies)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Currency not supported. Please try a different payment method.'
                ], 422);
            }

            $MERCHANT_KEY = config('payu.merchant_key');
            $SALT = config('payu.salt');
            $PAYU_BASE_URL = config('payu.base_url');

            if (!$MERCHANT_KEY || !$SALT || !$PAYU_BASE_URL) {
                throw new \Exception("PayU configuration is missing.");
            }
            $booking = Bookings::create([
                "product_id" => $request->input('service_id'),
                "branch_id" => $request->input('branch_id') ?? 0,
                "staff_id" => $request->input('staff_id') ?? 0,
                "slot_id" => $request->input('slot_id') ?? 0,
                "booking_date" => $formattedBookingDate,
                "from_time" => $request->input('from_time') ?? $fromTime,
                "to_time" => $request->input('to_time') ?? $toTime,
                "booking_status" => 1,
                "amount_tax" => $request->input('tax_amount'),
                "user_id" => $authId,
                "first_name" => $request->input('first_name'),
                "last_name" => $request->input('last_name'),
                "user_email" => $request->input('email'),
                "user_phone" => $request->input('phone_number'),
                "user_city" => $request->input('city'),
                "user_state" => $request->input('state'),
                "user_address" => $request->input('address'),
                "notes" => $request->input('note'),
                "user_postal" => $request->input('postal'),
                'tranaction' => '',
                "payment_type" => 8,
                "payment_status" => 1,
                "service_qty" => 1,
                "service_amount" => $request->input('sub_amount'),
                "total_amount" => $request->input('total_amount'),
                "additional_services" => $additionalServiceData,
            ]);

            $txnid = 'BOOK_' . $booking->id . '_' . time();
            $booking->update(['tranaction' => $txnid]);

            if ($booking) {
                $orderId = getBookingOrderId($booking->id);
                $booking->update(['order_id' => $orderId]);
            }

            if ($request->filled('coupon_id')) {
                DB::table('coupon_logs')->insert([
                    'user_id' => $booking->user_id,
                    'booking_id' => $booking->id,
                    'coupon_id' => $request->input('coupon_id'),
                    'coupon_code' => $request->input('coupon_code'),
                    'coupon_value' => $request->input('coupon_value'),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            $amount = number_format((float) $booking->total_amount, 2, '.', '');
            $productinfo = 'Booking: ' . $booking->order_id;
            $firstname = $booking->first_name;
            $email = $booking->user_email;
            $phone = preg_replace('/\D/', '', $booking->user_phone);

            $udf1 = $booking->id;
            $udf2 = $udf3 = $udf4 = $udf5 = $udf6 = $udf7 = $udf8 = $udf9 = $udf10 = '';

            $hashString = $MERCHANT_KEY . '|' . $txnid . '|' . $amount . '|' . $productinfo . '|' . $firstname . '|' . $email . '|' . $udf1 . '|' . $udf2 . '|' . $udf3 . '|' . $udf4 . '|' . $udf5 . '|' . $udf6 . '|' . $udf7 . '|' . $udf8 . '|' . $udf9 . '|' . $udf10 . '|' . $SALT;
            $hash = strtolower(hash('sha512', $hashString));

            $formFields = [
                'key' => $MERCHANT_KEY,
                'txnid' => $txnid,
                'amount' => $amount,
                'productinfo' => $productinfo,
                'firstname' => $firstname,
                'email' => $email,
                'phone' => $phone,
                'surl' => route('payu.success'),
                'furl' => route('payu.failure'),
                'hash' => $hash,
                'service_provider' => 'payu_paisa',
                'currency' => $currencyCode,
                'udf1' => $udf1,
                'udf2' => $udf2,
                'udf3' => $udf3,
                'udf4' => $udf4,
                'udf5' => $udf5,
                'udf6' => $udf6,
                'udf7' => $udf7,
                'udf8' => $udf8,
                'udf9' => $udf9,
                'udf10' => $udf10,
            ];

            return response()->json([
                'status' => 'success',
                'payment' => 'payu',
                'action' => $PAYU_BASE_URL . '/_payment',
                'fields' => $formFields
            ]);
        } catch (\Exception $e) {
            Log::error('PayU process failed: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'An error occurred with the payment provider.'], 500);
        }
    }


    /**
     * Handle the callback from PayU after payment attempt.
     * This version includes the definitive fix for the reverse hash verification.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function success(Request $request)
    {
        Log::info("PayU Success: ", $request->all());

        Bookings::where('tranaction', $request->txnid)->update([
            'payment_status' => 2, // Success
        ]);

        $booking = Bookings::where('tranaction', $request->txnid)->first();
        sendBookingNotification($booking->id ?? null);

        return redirect()->route('payment.two'); // Use your frontend success page
    }

    public function failed(Request $request)
    {
        Log::error("PayU Failed: ", $request->all());

        Bookings::where('tranaction', $request->txnid)->update([
            'payment_status' => 3, 
            'booking_status' => 3,
        ]);

        return redirect()->route('payment.two')->with('error', 'Payment failed.');
    }

    public function payuSubscription(Request $request)
    {
        try {
            $currencyCode = getDefaultCurrencyCode();
            $authId = Auth::id();
            $user = User::find($authId);

            if (!$user) {
                return response()->json([
                    'message' => 'User not found.'
                ], 404);
            }

            $allowedCurrencies = [
                'AED',
                'AFN',
                'ALL',
                'AMD',
                'ARS',
                'AUD',
                'AWG',
                'AZN',
                'BAM',
                'BDT',
                'BGN',
                'BHD',
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
                'CHF',
                'CLP',
                'CNY',
                'COP',
                'CRC',
                'CZK',
                'DKK',
                'DOP',
                'DZD',
                'EGP',
                'ETB',
                'EUR',
                'FJD',
                'GBP',
                'GHS',
                'GTQ',
                'HKD',
                'HNL',
                'HRK',
                'HTG',
                'HUF',
                'IDR',
                'ILS',
                'INR',
                'IQD',
                'IRR',
                'ISK',
                'JMD',
                'JOD',
                'JPY',
                'KES',
                'KHR',
                'KMF',
                'KRW',
                'KWD',
                'KZT',
                'LAK',
                'LBP',
                'LKR',
                'LYD',
                'MAD',
                'MDL',
                'MKD',
                'MMK',
                'MNT',
                'MOP',
                'MUR',
                'MVR',
                'MWK',
                'MXN',
                'MYR',
                'NAD',
                'NGN',
                'NIO',
                'NOK',
                'NPR',
                'NZD',
                'OMR',
                'PEN',
                'PGK',
                'PHP',
                'PKR',
                'PLN',
                'QAR',
                'RON',
                'RSD',
                'RUB',
                'SAR',
                'SCR',
                'SEK',
                'SGD',
                'SLL',
                'SOS',
                'SRD',
                'SSP',
                'SZL',
                'THB',
                'TND',
                'TOP',
                'TRY',
                'TTD',
                'TWD',
                'TZS',
                'UAH',
                'UGX',
                'USD',
                'UYU',
                'UZS',
                'VND',
                'XAF',
                'XCD',
                'XOF',
                'YER',
                'ZAR',
                'ZMW',
                'ZWL'
            ];

            if (!in_array(strtoupper($currencyCode), $allowedCurrencies)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Currency not supported. Please try a different payment method.'
                ], 422);
            }

            $MERCHANT_KEY = config('payu.merchant_key');
            $SALT = config('payu.salt');
            $PAYU_BASE_URL = config('payu.base_url');

            if (!$MERCHANT_KEY || !$SALT || !$PAYU_BASE_URL) {
                throw new \Exception("PayU configuration is missing.");
            }

            $amount = number_format((float) $request->service_amount, 2, '.', '');
            $productinfo = 'Sub: ' . $request->trx_id;
            $firstname = $user->email;
            $email = $user->email;
            $phone = preg_replace('/\D/', '', $user->phone_number);

            $udf1 = $request->trx_id;
            $udf2 = $udf3 = $udf4 = $udf5 = $udf6 = $udf7 = $udf8 = $udf9 = $udf10 = '';
            $txnid = 'BOOK_' . $request->trx_id . '_' . time();

            $hashString = $MERCHANT_KEY . '|' . $txnid . '|' . $amount . '|' . $productinfo . '|' . $firstname . '|' . $email . '|' . $udf1 . '|' . $udf2 . '|' . $udf3 . '|' . $udf4 . '|' . $udf5 . '|' . $udf6 . '|' . $udf7 . '|' . $udf8 . '|' . $udf9 . '|' . $udf10 . '|' . $SALT;
            $hash = strtolower(hash('sha512', $hashString));

            $formFields = [
                'key' => $MERCHANT_KEY,
                'txnid' => $txnid,
                'amount' => $amount,
                'productinfo' => $productinfo,
                'firstname' => $firstname,
                'email' => $email,
                'phone' => $phone,
                'surl' => url('/payu/sub/success'),
                'furl' => url('/provider/subscription'),
                'hash' => $hash,
                'service_provider' => 'payu_paisa',
                'currency' => $currencyCode,
                'udf1' => $udf1,
                'udf2' => $udf2,
                'udf3' => $udf3,
                'udf4' => $udf4,
                'udf5' => $udf5,
                'udf6' => $udf6,
                'udf7' => $udf7,
                'udf8' => $udf8,
                'udf9' => $udf9,
                'udf10' => $udf10,
            ];

            PackageTrx::where('id', $request->trx_id)->update([
                'transaction_id' => $txnid
            ]);

            return response()->json([
                'status' => 'success',
                'payment' => 'payu',
                'action' => $PAYU_BASE_URL . '/_payment',
                'fields' => $formFields
            ]);
        } catch (\Exception $e) {
            Log::error('PayU process failed: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'An error occurred with the payment provider.'], 500);
        }
    }

    public function payuSubscriptionSuccess(Request $request)
    {

        PackageTrx::where('transaction_id', $request->razorpay_order_id)->update(['payment_status' => 2, 'status' => 1]);
        return view('provider.subscription.payment_success');
    }



    public function storePayU(Request $request)
    {
        $request->validate([
            'payu_merchant_key'  => 'required|string',
            'payu_merchant_salt' => 'required|string',
            'payu_base_url'      => 'required|url',
            'payu_mode'          => 'required|in:test,live',
        ]);

        try {
            // Prepare env updates
            $envUpdates = [
                'PAYU_MERCHANT_KEY'  => $request->payu_merchant_key,
                'PAYU_MERCHANT_SALT' => $request->payu_merchant_salt,
                'PAYU_BASE_URL'      => $request->payu_base_url,
                'PAYU_MODE'          => $request->payu_mode === 'test' ? 'sandbox' : 'production',
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
                'payu_merchant_key'  => $request->payu_merchant_key,
                'payu_merchant_salt' => $request->payu_merchant_salt,
                'payu_base_url'      => $request->payu_base_url,
                'payu_mode'          => $request->payu_mode, // store test/live in DB
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
                'message' => 'Error! updating PayU settings: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the status of the PayU payment gateway.
     */
    public function statusPayU(Request $request)
    {
        DB::table('general_settings')->updateOrInsert(
            [
                'key' => 'payu_status',
                'group_id' => 13, // Assuming group_id 4 is for payment credentials
            ],
            [
                'value' => $request->payu_status,
                'updated_at' => now(),
            ]
        );

        DB::table('payment_methods')->updateOrInsert(
            [
                'payment_type' => 'PayU',
            ],
            [
                'label'      => 'payu',
                'status'     => $request->payu_status,
                'created_by' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $message = $request->payu_status == 1
            ? 'PayU status enabled'
            : 'PayU status disabled';

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

        $validator = Validator::make($request->all(), [
            'service_id'   => 'required|integer',
            'total_amount' => 'required|numeric|min:1',
            'sub_amount'   => 'required|numeric',
            'tax_amount'   => 'required|numeric',
            'first_name'   => 'required|string|max:255',
            'email'        => 'required|email',
            'phone_number' => 'required|string|max:20',
            'booking_date' => 'required|date',
            'from_time'    => 'required',
            'to_time'      => 'required',
            'city'         => 'required|string',
            'state'        => 'required|string',
            'address'      => 'required|string',
            'postal'       => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'Validation failed.', 'errors' => $validator->errors()], 422);
        }

        try {
            $MERCHANT_KEY = config('services.payu.key');
            $SALT = config('services.payu.salt');
            $isProduction = config('services.payu.mode') === 'production';

            $booking = Bookings::create([
                "product_id"     => $request->service_id,
                "branch_id"      => $request->branch_id ?? 0,
                "staff_id"       => $request->staff_id ?? 0,
                "slot_id"        => $request->slot_id ?? 0,
                "booking_date" => $bookingDate,
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
                "notes"           => $request->note,
                "user_postal"    => $request->postal,
                "payment_type"   => 8,
                "service_qty"    => 1,
                "service_amount" => $request->sub_amount,
                "total_amount"   => $request->total_amount,
            ]);

            $txnid = 'BOOK_' . $booking->id . '_' . time();
            $booking->update(['tranaction' => $txnid]);

            $amount = number_format((float) $booking->total_amount, 2, '.', '');
            $productinfo = 'Booking: ' . getBookingOrderId($booking->id);
            $hashString = $MERCHANT_KEY . '|' . $txnid . '|' . $amount . '|' . $productinfo . '|' . $booking->first_name . '|' . $booking->user_email . '|||||||||||' . $SALT;
            $hash = strtolower(hash('sha512', $hashString));

            if ($booking) {
                $orderId = getBookingOrderId($booking->id);
                $booking->update(['order_id' => $orderId]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'PayU parameters generated successfully.',
                'data' => [
                    'key'         => $MERCHANT_KEY,
                    'txnid'       => $txnid,
                    'amount'      => $amount,
                    'productinfo' => $productinfo,
                    'firstname'   => $booking->first_name,
                    'email'       => $booking->user_email,
                    'phone'       => preg_replace('/\D/', '', $booking->user_phone),
                    'surl'        => 'https://api.yourdomain.com/payment/callback',
                    'furl'        => 'https://api.yourdomain.com/payment/callback',
                    'hash'        => $hash,
                    'booking_id'   => $booking->id, // optional
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'An error occurred with the payment provider.'], 500);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'txnid'   => 'required|string',
            'booking_id' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid data provided', 'errors' => $validator->errors()], 422);
        }

        $orderId = $request->input('order_id');

        try {
            $booking = Bookings::where('tranaction', $request->txnid)
                ->firstOrFail();

            sendBookingNotification($booking->id ?? null);;
;

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
