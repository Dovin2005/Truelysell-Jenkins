<?php

namespace Modules\PaymentGateway\app\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Bookings;
use App\Models\PackageTrx;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;

class MercadoPagoController extends Controller
{
    /**
     * Handle the payment initiation with Mercado Pago.
     */
    public function handlePayment(Request $request, $formattedBookingDate, $authId, $additionalServiceData, $fromTime, $toTime)
    {
        $validator = Validator::make($request->all(), [
            'service_id'   => 'required',
            'tax_amount'   => 'required|numeric',
            'first_name'   => 'required|string|max:255',
            'last_name'    => 'required|string|max:255',
            'email'        => 'required|email',
            'phone_number' => 'required|string|max:20',
            'city'         => 'required|string',
            'state'        => 'required|string',
            'address'      => 'required|string',
            'postal'       => 'required|string',
            'total_amount' => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed.', 'errors' => $validator->errors()], 422);
        }

        $localOrderId = 'booking_' . $authId . '_' . time();

        $booking = Bookings::create([
            'product_id'          => $request->service_id,
            'branch_id'           => $request->branch_id ?? 0,
            'staff_id'            => $request->staff_id ?? 0,
            'slot_id'             => $request->slot_id ?? 0,
            'booking_date'        => $formattedBookingDate,
            'from_time'           => $request->from_time ?? $fromTime,
            'to_time'             => $request->to_time ?? $toTime,
            'booking_status'      => 1,
            'payment_status'      => 1,
            'amount_tax'          => $request->tax_amount,
            'user_id'             => $authId,
            'first_name'          => $request->first_name,
            'last_name'           => $request->last_name,
            'user_email'          => $request->email,
            'user_phone'          => $request->phone_number,
            'user_city'           => $request->city,
            'user_state'          => $request->state,
            'user_address'        => $request->address,
            'note'                => $request->note,
            'user_postal'         => $request->postal,
            'tranaction'          => $localOrderId,
            'payment_type'        => 12,
            'service_qty'         => 1,
            'service_amount'      => $request->sub_amount,
            'total_amount'        => $request->total_amount,
            'additional_services' => $additionalServiceData ?? null,
        ]);

        if ($booking) {
            $bookingOrderId = getBookingOrderId($booking->id);
            $booking->update(['order_id' => $bookingOrderId]);
        }

        $phone = preg_replace('/\D/', '', $request->phone_number);

        $payload = [
            'items' => [[
                'title'       => 'Service Booking #' . $booking->id,
                'quantity'    => 1,
                'currency_id' => 'MXN',
                'unit_price'  => (float) $request->total_amount
            ]],
            'payer' => [
                'name'    => $request->first_name,
                'surname' => $request->last_name,
                'email'   => $request->email,
                'phone'   => [
                    'area_code' => substr($phone, 0, -10),
                    'number'    => substr($phone, -10)
                ],
            ],
            'back_urls' => [
                'success' => 'https://truelysell-dev.dreamstechnologies.com/payment-success',
                'failure' => 'https://truelysell-dev.dreamstechnologies.com/payment-success',
                'pending' => 'https://truelysell-dev.dreamstechnologies.com/payment-success',
            ],
            'auto_return'        => 'approved',
            'external_reference' => $localOrderId
        ];
        $response = Http::withToken(env('MERCADOPAGO_ACCESS_TOKEN'))
            ->post('https://api.mercadopago.com/checkout/preferences', $payload);

        if ($response->successful() && isset($response['init_point'])) {
            return response()->json(['mercadopago' => ['redirect_url' => $response['init_point']]]);
        }

        Log::error('MercadoPago Init Failed', ['response_body' => $response->json(), 'sent_payload' => $payload]);
        $booking->delete();
        return response()->json(['message' => 'Payment gateway error. Please check logs.'], 502);
    }

    /**
     * Handle the success callback from Mercado Pago.
     */
    public function success(Request $request)
    {
        $orderId = $request->get('external_reference');
        if ($orderId) {
            Bookings::where('tranaction', $orderId)->where('payment_status', 1)->update(['payment_status' => 2]); // 2 = Paid
            $booking = Bookings::where('tranaction', $orderId)->first();
            sendBookingNotification($booking->id ?? null);
        }
        return redirect()->route('payment.two'); // Redirect to your success page
    }

    /**
     * Handle the failure callback from Mercado Pago.
     */
    public function failure(Request $request)
    {
        $orderId = $request->get('external_reference');
        if ($orderId) {
            Bookings::where('tranaction', $orderId)->where('payment_status', 1)->update(['payment_status' => 3]); // 3 = Failed
        }
        return redirect()->route('your.payment.failed.route'); // Redirect to your failure page
    }

    /**
     * Handle the pending callback from Mercado Pago.
     */
    public function pending(Request $request)
    {
        // The booking status is already pending, so no database update is needed.
        // You can simply redirect the user to a "payment pending" page.
        return redirect()->route('your.payment.pending.route');
    }

    /**
     * Store or update Mercado Pago credentials.
     */
    public function storeMercadoPago(Request $request)
    {
        $request->validate([
            'mercadopago_public_key'  => 'required|string',
            'mercadopago_access_token' => 'required|string',
            'mercadopago_callback_url' => 'required|url',
        ]);

        try {
            // Prepare env updates
            $envUpdates = [
                'MERCADOPAGO_PUBLIC_KEY'   => $request->mercadopago_public_key,
                'MERCADOPAGO_ACCESS_TOKEN' => $request->mercadopago_access_token,
                'MERCADOPAGO_CALLBACK_URL' => $request->mercadopago_callback_url,
            ];

            // Step 1: Update .env file
            $envPath = base_path('.env');
            if (file_exists($envPath)) {
                $envContent = file_get_contents($envPath);

                foreach ($envUpdates as $key => $value) {
                    // Add quotes around values that contain spaces
                    $formattedValue = str_contains($value, ' ') ? "\"{$value}\"" : $value;
                    $pattern = "/^{$key}=.*$/m";
                    $line = "{$key}={$formattedValue}";

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
                'mercadopago_public_key'   => $request->mercadopago_public_key,
                'mercadopago_access_token' => $request->mercadopago_access_token,
                'mercadopago_callback_url' => $request->mercadopago_callback_url,
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
                'message' => 'Error! updating Mercado Pago settings: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the status of the Mercado Pago payment gateway.
     */
    public function statusMercadoPago(Request $request)
    {
        DB::table('general_settings')->updateOrInsert(
            [
                'key' => 'mercadopago_status',
                'group_id' => 13, // Assuming group_id 4 is for payment credentials
            ],
            [
                'value' => $request->mercadopago_status,
                'updated_at' => now(),
            ]
        );

        DB::table('payment_methods')->updateOrInsert(
            [
                'payment_type' => 'Mercado Pago',
            ],
            [
                'label'      => 'mercadopago',
                'status'     => $request->mercadopago_status,
                'created_by' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $message = $request->mercadopago_status == 1
            ? 'Mercado Pago status enabled'
            : 'Mercado Pago status disabled';

        return response()->json([
            'code' => 200,
            'message' => $message,
        ]);
    }

    public function mercadopagoSubscription(Request $request)
    {
        $authId = Auth::id();
        $user = User::find($authId);

        if (!$user) {
            return response()->json([
                'message' => 'User not found.'
            ], 404);
        }

        $currencyCode = getDefaultCurrencyCode();
        $allowedCurrencies = ['MXN'];

        if (!in_array(strtoupper($currencyCode), $allowedCurrencies)) {
            return response()->json([
                'error' => 'Currency not supported. Please try a different payment method.'
            ], 500);
        }

        $localOrderId = 'booking_' . $authId . '_' . time();

        $phone = preg_replace('/\D/', '', $user->phone_number);

        $payload = [
            'items' => [[
                'title'       => 'Subscription',
                'quantity'    => 1,
                'currency_id' => "MXN",
                'unit_price'  => (float) $request->service_amount
            ]],
            'payer' => [
                'name'    => $user->name,
                'email'   => $user->email,
                'phone'   => [
                    'area_code' => substr($phone, 0, -10),
                    'number'    => substr($phone, -10)
                ],
            ],
            'back_urls' => [
                'success' => 'https://truelysell-dev.dreamstechnologies.com/payment-success',
                'failure' => 'https://truelysell-dev.dreamstechnologies.com/payment-success',
                'pending' => 'https://truelysell-dev.dreamstechnologies.com/payment-success',
            ],
            'auto_return'        => 'approved',
            'external_reference' => $localOrderId
        ];
        $response = Http::withToken(env('MERCADOPAGO_ACCESS_TOKEN'))
            ->post('https://api.mercadopago.com/checkout/preferences', $payload);

        PackageTrx::where('id', $request->trx_id)->update([
            'transaction_id' => $localOrderId
        ]);

        if ($response->successful() && isset($response['init_point'])) {
            return response()->json(['mercadopago' => ['redirect_url' => $response['init_point']]]);
        } else {
            Log::error('mercadopago Init Failed: ' . $response->body());
            return response()->json([
                'error' => 'Payment initialization failed.'
            ], 500);
        }
    }

    public function mercadopagoSubscriptionSuccess(Request $request)
    {
        $orderId = $request->get('external_reference');

        if ($orderId) {
            PackageTrx::where('transaction_id', $orderId)->update([
                'payment_status' => 2,
            ]);

            return view('provider.subscription.payment_success');
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function initiatePayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_id'   => 'required',
            'total_amount' => 'required|numeric|min:1',
            'first_name'   => 'required|string|max:255',
            'email'        => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'Validation failed.', 'errors' => $validator->errors()], 422);
        }

        $bookingDate = null;
        if ($request->filled('booking_date')) {
            $bookingDate = Carbon::parse($request->input('booking_date'))->format('Y-m-d');
        }

        try {
            $externalReference = 'booking_' . $request->user_id . '_' . time();

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
                'tranaction'     => "",
                "payment_type"   => 8,
                "service_qty"    => 1,
                "service_amount" => $request->sub_amount,
                "total_amount"   => $request->total_amount,
            ]);

            if ($booking) {
                $bookingOrderId = getBookingOrderId($booking->id);
                $booking->update(['order_id' => $bookingOrderId]);
            }

            $payload = [
                'items' => [[
                    'title'       => 'Service Booking #' . $booking->id,
                    'quantity'    => 1,
                    'currency_id' => getDefaultCurrencyCode(), // e.g., 'MXN', 'BRL', 'ARS'
                    'unit_price'  => (float) number_format($request->total_amount, 2, '.', ''),
                ]],
                'payer' => [
                    'name'    => $request->first_name,
                    'surname' => $request->last_name,
                    'email'   => $request->email,
                ],
                'external_reference' => $externalReference,
            ];

            $response = Http::withToken(config('services.mercadopago.token'))
                ->post('https://api.mercadopago.com/checkout/preferences', $payload);

            $mpData = $response->json();

            Bookings::where('id', $booking->id)->update([
                'tranaction' => $mpData['id']
            ]);

            if (!$response->successful() || !isset($mpData['id'])) {
                Log::error('MercadoPago Mobile Init Failed', ['response' => $response->body()]);
                $booking->delete(); // Clean up the failed booking.
                return response()->json(['status' => 'error', 'message' => 'Payment gateway failed to initialize.'], 502);
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'MargofodPay preference created successfully.',
                'data'    => [
                    'preference_id'       => $mpData['id'] ?? null,
                    'init_point'          => $mpData['init_point'] ?? null,
                    'sandbox_init_point'  => $mpData['sandbox_init_point'] ?? null,
                    'public_key'    => config('services.mercadopago.key'),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('MercadoPago Mobile Initiate Exception: ' . $e->getMessage());
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
