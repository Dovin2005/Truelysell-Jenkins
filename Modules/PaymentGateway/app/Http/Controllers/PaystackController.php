<?php

namespace Modules\PaymentGateway\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bookings;
use App\Models\PackageTrx;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PaystackController extends Controller
{
    /**
     * Handle Paystack Payment and Booking Creation
     */
    public function handlePayment(Request $request, $formattedBookingDate, $authId, $additionalServiceData, $fromTime, $toTime)
    {
        // Step 1: Validate required fields
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
                    'error' => 'Paystack is not available at the moment. Please try other payment methods or contact admin.'
                ], 500);
            }
        }

        // Step 2: Validate supported currencies
        $currencyCode = getDefaultCurrencyCode();
        $allowedCurrencies = ['ZAR']; // Add more if needed

        if (!in_array(strtoupper($currencyCode), $allowedCurrencies)) {
            return response()->json([
                'message' => 'Currency not supported. Please try a different payment method.'
            ], 422);
        }

        // Step 3: Create local booking
        $localOrderId = 'booking_' . $authId . '_' . time();

        $booking = Bookings::create([
            "product_id"          => $request->input('service_id'),
            "branch_id"           => $request->input('branch_id') ?? 0,
            "staff_id"            => $request->input('staff_id') ?? 0,
            "slot_id"             => $request->input('slot_id') ?? 0,
            "booking_date"        => $formattedBookingDate,
            "from_time"           => $request->input('from_time') ?? $fromTime,
            "to_time"             => $request->input('to_time') ?? $toTime,
            "booking_status"      => 1, // Pending
            "payment_status"      => 1, // Pending
            "amount_tax"          => $request->input('tax_amount'),
            "user_id"             => $authId,
            "first_name"          => $request->input('first_name'),
            "last_name"           => $request->input('last_name'),
            "user_email"          => $request->input('email'),
            "user_phone"          => $request->input('phone_number'),
            "user_city"           => $request->input('city'),
            "user_state"          => $request->input('state'),
            "user_address"        => $request->input('address'),
            "notes"                => $request->input('note'),
            "user_postal"         => $request->input('postal'),
            "tranaction"          => $localOrderId,
            "payment_type"        => 11, // Paystack
            "service_qty"         => 1,
            "service_amount"      => $request->input('sub_amount'),
            "total_amount"        => $request->input('total_amount'),
        ]);

        if ($additionalServiceData) {
            $booking->update(['additional_services' => $additionalServiceData]);
        }

        if ($booking) {
            $bookingOrderId = getBookingOrderId($booking->id);
            $booking->update(['order_id' => $bookingOrderId]);
        }

        // Step 4: Call Paystack API
        $amountKobo = $request->input('total_amount') * 100;

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('PAYSTACK_SECRET_KEY'),
            'Content-Type'  => 'application/json',
        ])->post('https://api.paystack.co/transaction/initialize', [
            'email'        => $request->input('email'),
            'amount'       => $amountKobo,
            'currency'     => strtoupper($currencyCode),
            'reference'    => $localOrderId,
            'callback_url' => route('paystack.success'),
        ]);

        if ($response->successful() && isset($response->json()['data']['authorization_url'])) {
            return response()->json([
                'paystack' => [
                    'redirect_url' => $response->json()['data']['authorization_url']
                ]
            ]);
        } else {
            Log::error('Paystack Init Failed: ' . $response->body());
            return response()->json([
                'error' => 'Payment initialization failed.'
            ], 500);
        }
    }

    /**
     * Paystack Callback Handler
     */
    public function success(Request $request)
    {
        $trxref = $request->query('reference');

        if (!$trxref) {
            return redirect()->route('payment.failed_page')->with('error', 'Missing transaction reference.');
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('PAYSTACK_SECRET_KEY'),
        ])->get('https://api.paystack.co/transaction/verify/' . $trxref);

        if ($response->successful() && $response->json('data.status') === 'success') {
            Bookings::where('tranaction', $trxref)->update(['payment_status' => 2]);

            $booking = Bookings::where('tranaction', $trxref)->first();
            sendBookingNotification($booking->id ?? null);

            return redirect()->route('payment.two');
        }

        return redirect()->route('payment.failed_page')->with('error', 'Payment verification failed.');
    }

    public function paystackSubscription(Request $request)
    {
        $authId = Auth::id();
        $user = User::find($authId);

        if (!$user) {
            return response()->json([
                'message' => 'User not found.'
            ], 404);
        }
        $currencyCode = getDefaultCurrencyCode();
        $allowedCurrencies = ['ZAR'];

        if (!in_array(strtoupper($currencyCode), $allowedCurrencies)) {
            return response()->json([
                'error' => 'Currency not supported. Please try a different payment method.'
            ], 500);
        }

        $localOrderId = 'booking_' . $authId . '_' . time();

        $amountKobo = $request->input('service_amount') * 100;

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('PAYSTACK_SECRET_KEY'),
            'Content-Type'  => 'application/json',
        ])->post('https://api.paystack.co/transaction/initialize', [
            'email'        => $user->email,
            'amount'       => $amountKobo,
            'currency'     => strtoupper($currencyCode),
            'reference'    => $localOrderId,
            'callback_url' => route('paystack.sub.success'),
        ]);

        PackageTrx::where('id', $request->trx_id)->update([
            'transaction_id' => $localOrderId
        ]);

        if ($response->successful() && isset($response->json()['data']['authorization_url'])) {
            return response()->json([
                'paystack' => [
                    'redirect_url' => $response->json()['data']['authorization_url']
                ]
            ]);
        } else {
            Log::error('Paystack Init Failed: ' . $response->body());
            return response()->json([
                'error' => 'Payment initialization failed.'
            ], 500);
        }
    }

    public function paystackSubscriptionSuccess(Request $request)
    {
        $trxref = $request->query('reference'); // Paystack transaction reference

        if (!$trxref) {
            return redirect()->route('payment.failed_page')->with('error', 'Missing transaction reference.');
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('PAYSTACK_SECRET_KEY'),
        ])->get('https://api.paystack.co/transaction/verify/' . $trxref);

        if ($response->successful() && $response->json('data.status') === 'success') {
            //  Update the transaction as successful
            PackageTrx::where('transaction_id', $trxref)->update([
                'payment_status' => 2, // 2 = paid
                'status' => 1         // optionally also mark status = 1 (confirmed)
            ]);

            return view('provider.subscription.payment_success');
        }

        return redirect()->route('payment.failed_page')->with('error', 'Payment verification failed.');
    }

    public function storePaystack(Request $request)
    {
        $request->validate([
            'paystack_public_key'   => 'required|string',
            'paystack_secret_key'  => 'required|string',
            'paystack_payment_url' => 'required|url',
            'paystack_callback_url' => 'required|url',
        ]);

        try {
            // Prepare env updates
            $envUpdates = [
                'PAYSTACK_PUBLIC_KEY'   => $request->paystack_public_key,
                'PAYSTACK_SECRET_KEY'  => $request->paystack_secret_key,
                'PAYSTACK_PAYMENT_URL' => $request->paystack_payment_url,
                'PAYSTACK_CALLBACK_URL' => $request->paystack_callback_url,
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
                'paystack_public_key'   => $request->paystack_public_key,
                'paystack_secret_key'  => $request->paystack_secret_key,
                'paystack_payment_url' => $request->paystack_payment_url,
                'paystack_callback_url' => $request->paystack_callback_url,
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
                'message' => 'Error! updating Paystack settings: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the status of the Paystack payment gateway.
     */
    public function statusPaystack(Request $request)
    {

        DB::table('general_settings')->updateOrInsert(
            [
                'key' => 'paystack_status',
                'group_id' => 13, // Assuming group_id 4 is for payment credentials
            ],
            [
                'value' => $request->paystack_status,
                'updated_at' => now(),
            ]
        );

        DB::table('payment_methods')->updateOrInsert(
            [
                'payment_type' => 'Paystack',
            ],
            [
                'label'      => 'paystack',
                'status'     => $request->paystack_status,
                'created_by' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $message = $request->paystack_status == 1
            ? 'Paystack status enabled'
            : 'Paystack status disabled';

        return response()->json([
            'code' => 200,
            'message' => $message,
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function initiatePayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_id'   => 'required|integer',
            'total_amount' => 'required|numeric|min:1',
            'email'        => 'required|email',
            'first_name'   => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'Validation failed.', 'errors' => $validator->errors()], 422);
        }

        try {
            $reference = 'booking_' . $request->user_id . '_' . time();

            $booking = Bookings::create([
                "product_id"     => $request->service_id,
                "branch_id"      => $request->branch_id ?? 0,
                "staff_id"       => $request->staff_id ?? 0,
                "slot_id"        => $request->slot_id ?? 0,
                "booking_date"   => $request->booking_date,
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
                'tranaction'     => $reference,
                "payment_type"   => 8,
                "service_qty"    => 1,
                "service_amount" => $request->sub_amount,
                "total_amount"   => $request->total_amount,
            ]);

            $amountInSmallestUnit = $request->input('total_amount') * 100;
            $currencyCode = getDefaultCurrencyCode();

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.paystack.secret'),
                'Content-Type'  => 'application/json',
                'Cache-Control' => 'no-cache',
            ])->post('https://api.paystack.co/transaction/initialize', [
                'email'    => $request->input('email'),
                'amount'   => $amountInSmallestUnit,
                'currency' => strtoupper($currencyCode),
                'reference' => $reference,
                // The callback_url is not critical for mobile SDK, but can be a backup
                'callback_url' => 'https://api.yourdomain.com/payment/fallback',
            ]);

            $paystackData = $response->json();

            if (!$response->successful() || !isset($paystackData['data']['access_code'])) {
                Log::error('Paystack Mobile Init Failed: ' . $response->body());
                $booking->delete(); // Clean up the failed booking attempt.
                return response()->json(['status' => 'error', 'message' => 'Payment gateway failed to initialize.'], 502);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Paystack transaction initialized successfully.',
                'data' => [
                    'access_code' => $paystackData['data']['access_code'],
                    'reference'   => $paystackData['data']['reference'],
                    'public_key'  => config('services.paystack.public'),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Paystack Mobile Initiate Exception: ' . $e->getMessage());
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
