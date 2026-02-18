<?php

namespace Modules\PaymentGateway\app\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Bookings;
use App\Models\PackageTrx;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;

class AuthorizeNetController extends Controller
{
    private $endpoint = 'https://apitest.authorize.net/xml/v1/request.api';

    public function handlePayment(Request $request, $formattedBookingDate, $authId, $additionalServiceData, $fromTime, $toTime)
    {
        try {
            // Step 1: Validate input
            $request->validate([
                'card_number' => 'required',
                'card_expiry_month' => 'required',
                'card_expiry_year' => 'required',
                'card_cvv' => 'required',
                'total_amount' => 'required|numeric',
            ]);

            $currencyCode = getDefaultCurrencyCode();
            $supportedCurrencies = [
                // North America
                'USD', // US Dollar
                'CAD', // Canadian Dollar

                // United Kingdom & Europe
                'EUR', // Euro
                'GBP', // British Pound
                'CHF', // Swiss Franc
                'DKK', // Danish Krone
                'NOK', // Norwegian Krone
                'PLN', // Polish Zloty
                'SEK', // Swedish Krona

                // Australia & Oceania
                'AUD', // Australian Dollar
                'NZD', // New Zealand Dollar

                // Others officially supported
                'JPY', // Japanese Yen
                'ZAR', // South African Rand
            ];


            if (!in_array(strtoupper($currencyCode), $supportedCurrencies)) {
                return response()->json([
                    'message' => 'Currency not supported. Please try a different payment method.'
                ], 400);
            }

            // Step 2: Prepare payload for Authorize.Net
            $expirationDate = $request->card_expiry_year . '-' . str_pad($request->card_expiry_month, 2, '0', STR_PAD_LEFT);

            $payload = [
                'createTransactionRequest' => [
                    'merchantAuthentication' => [
                        'name' => env('AUTHORIZE_NET_API_LOGIN_ID'),
                        'transactionKey' => env('AUTHORIZE_NET_TRANSACTION_KEY'),
                    ],
                    'transactionRequest' => [
                        'transactionType' => 'authCaptureTransaction',
                        'amount' => $request->total_amount,
                        'payment' => [
                            'creditCard' => [
                                'cardNumber' => $request->card_number,
                                'expirationDate' => $expirationDate,
                                'cardCode' => $request->card_cvv,
                            ],
                        ],
                    ],
                ],
            ];

            // Step 3: Send request to Authorize.Net
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($this->endpoint, $payload);

            $rawBody = $response->body();
            $cleanJson = preg_replace('/^\xEF\xBB\xBF/', '', $rawBody);
            $data = json_decode($cleanJson, true);

            // Step 4: Save Booking Data
            $datas = [
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
                "payment_type" => 10,
                "payment_status" => 1,
                "service_qty" => 1,
                "service_amount" => $request->input('sub_amount'),
                "total_amount" => $request->input('total_amount')
            ];

            if ($additionalServiceData) {
                $datas['additional_services'] = $additionalServiceData;
            }

            $save = Bookings::create($datas);

            if ($save) {
                $orderId = getBookingOrderId($save->id);
                $save->update(['order_id' => $orderId]);
            }

            // Step 5: Check payment response
            if (
                isset($data['transactionResponse']['responseCode']) &&
                $data['transactionResponse']['responseCode'] == '1'
            ) {
                sendBookingNotification($save->id);

                return response()->json([
                    'code' => 200,
                    'message' => 'Booking successfully created!',
                    'data' => ['order_id' => $save->order_id]
                ]);
            }

            // Transaction failed — return detailed error
            return response()->json([
                'code' => 500,
                'message' => 'Authorize.Net is currently unavailable. Please try again later.',
                'details' => $data['transactionResponse'] ?? $data
            ], 500);
        } catch (\Throwable $e) {
            // Catch any errors including network failures
            Log::error('Authorize.Net Payment Error: ' . $e->getMessage());

            return response()->json([
                'code' => 503,
                'message' => 'Authorize.Net is currently unavailable. Please try again later.',
                'error' => $e->getMessage()
            ], 503);
        }
    }

    public function authorizeSubscription(Request $request)
    {
        try {
            $request->validate([
                'card_number' => 'required',
                'card_expiry_month' => 'required',
                'card_expiry_year' => 'required',
                'card_cvv' => 'required',
                'serviceamount' => 'required|numeric',
            ]);

            $currencyCode = getDefaultCurrencyCode();
            $supportedCurrencies = [
                // North America
                'USD', // US Dollar
                'CAD', // Canadian Dollar

                // United Kingdom & Europe
                'EUR', // Euro
                'GBP', // British Pound
                'CHF', // Swiss Franc
                'DKK', // Danish Krone
                'NOK', // Norwegian Krone
                'PLN', // Polish Zloty
                'SEK', // Swedish Krona

                // Australia & Oceania
                'AUD', // Australian Dollar
                'NZD', // New Zealand Dollar

                // Others officially supported
                'JPY', // Japanese Yen
                'ZAR', // South African Rand
            ];


            if (!in_array(strtoupper($currencyCode), $supportedCurrencies)) {
                return response()->json([
                    'message' => 'Currency not supported. Please try a different payment method.'
                ], 400);
            }

            $expirationDate = $request->card_expiry_year . '-' . str_pad($request->card_expiry_month, 2, '0', STR_PAD_LEFT);

            $payload = [
                'createTransactionRequest' => [
                    'merchantAuthentication' => [
                        'name' => env('AUTHORIZE_NET_API_LOGIN_ID'),
                        'transactionKey' => env('AUTHORIZE_NET_TRANSACTION_KEY'),
                    ],
                    'transactionRequest' => [
                        'transactionType' => 'authCaptureTransaction',
                        'amount' => $request->serviceamount,
                        'payment' => [
                            'creditCard' => [
                                'cardNumber' => $request->card_number,
                                'expirationDate' => $expirationDate,
                                'cardCode' => $request->card_cvv,
                            ],
                        ],
                    ],
                ],
            ];

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($this->endpoint, $payload);

            $rawBody = $response->body();
            $cleanJson = preg_replace('/^\xEF\xBB\xBF/', '', $rawBody);
            $data = json_decode($cleanJson, true);

            if (
                isset($data['transactionResponse']['responseCode']) &&
                $data['transactionResponse']['responseCode'] == '1'
            ) {
                PackageTrx::where('id', $request->trx_id)->update(['payment_status' => 2, 'status' => 1]);

                return view('provider.subscription.payment_success');
            }

            return response()->json([
                'code' => 500,
                'message' => 'Authorize.Net is currently unavailable. Please try again later.',
                'details' => $data['transactionResponse'] ?? $data
            ], 500);
        } catch (\Throwable $e) {
            // Catch any errors including network failures
            Log::error('Authorize.Net Payment Error: ' . $e->getMessage());

            return response()->json([
                'code' => 503,
                'message' => 'Authorize.Net is currently unavailable. Please try again later.',
                'error' => $e->getMessage()
            ], 503);
        }
    }

    public function storeAuthorizeNet(Request $request)
    {
        $request->validate([
            'authorizenet_api_login_id'   => 'required|string',
            'authorizenet_transaction_key' => 'required|string',
            'authorizenet_env'             => 'required|in:test,live',
        ]);

        try {
            // Determine mode for .env file (sandbox/production)
            $inputMode = $request->authorizenet_env; // 'test' or 'live' from form
            $envMode   = $inputMode === 'test' ? 'sandbox' : 'production';

            // Prepare env updates
            $envUpdates = [
                'AUTHORIZE_NET_API_LOGIN_ID'   => $request->authorizenet_api_login_id,
                'AUTHORIZE_NET_TRANSACTION_KEY' => $request->authorizenet_transaction_key,
                'AUTHORIZE_NET_ENV'             => $envMode,
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
                'authorizenet_api_login_id'   => $request->authorizenet_api_login_id,
                'authorizenet_transaction_key' => $request->authorizenet_transaction_key,
                'authorizenet_env'             => $inputMode,
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
                'message' => 'Error! updating Authorize.Net settings: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the status of the Authorize.Net payment gateway.
     */
    public function statusAuthorizeNet(Request $request)
    {
        DB::table('general_settings')->updateOrInsert(
            [
                'key' => 'authorizenet_status',
                'group_id' => 13, // Assuming group_id 4 is for payment credentials
            ],
            [
                'value' => $request->authorizenet_status,
                'updated_at' => now(),
            ]
        );

        DB::table('payment_methods')->updateOrInsert(
            [
                'payment_type' => 'Authorize.net',
            ],
            [
                'label'      => 'authorizenet',
                'status'     => $request->authorizenet_status,
                'created_by' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $message = $request->authorizenet_status == 1
            ? 'Authorize.Net status enabled'
            : 'Authorize.Net status disabled';

        return response()->json([
            'code' => 200,
            'message' => $message,
        ]);
    }


    public function initiatePayment(Request $request)
    {
        // Step 1: Validate input
        $request->validate([
            'card_number' => 'required',
            'card_expiry_month' => 'required',
            'card_expiry_year' => 'required',
            'card_cvv' => 'required',
            'total_amount' => 'required|numeric',
        ]);

        $currencyCode = getDefaultCurrencyCode();
        $supportedCurrencies = [
            // North America
            'USD', // US Dollar
            'CAD', // Canadian Dollar

            // United Kingdom & Europe
            'EUR', // Euro
            'GBP', // British Pound
            'CHF', // Swiss Franc
            'DKK', // Danish Krone
            'NOK', // Norwegian Krone
            'PLN', // Polish Zloty
            'SEK', // Swedish Krona

            // Australia & Oceania
            'AUD', // Australian Dollar
            'NZD', // New Zealand Dollar

            // Others officially supported
            'JPY', // Japanese Yen
            'ZAR', // South African Rand
        ];


        if (!in_array(strtoupper($currencyCode), $supportedCurrencies)) {
            return response()->json([
                'message' => 'Currency not supported. Please try a different payment method.'
            ], 400);
        }

        // Step 2: Prepare payload for Authorize.Net
        $expirationDate = $request->card_expiry_year . '-' . str_pad($request->card_expiry_month, 2, '0', STR_PAD_LEFT);

        $payload = [
            'createTransactionRequest' => [
                'merchantAuthentication' => [
                    'name' => env('AUTHORIZE_NET_API_LOGIN_ID'),
                    'transactionKey' => env('AUTHORIZE_NET_TRANSACTION_KEY'),
                ],
                'transactionRequest' => [
                    'transactionType' => 'authCaptureTransaction',
                    'amount' => $request->total_amount,
                    'payment' => [
                        'creditCard' => [
                            'cardNumber' => $request->card_number,
                            'expirationDate' => $expirationDate,
                            'cardCode' => $request->card_cvv,
                        ],
                    ],
                ],
            ],
        ];

        // Step 3: Send request to Authorize.Net
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($this->endpoint, $payload);

        $rawBody = $response->body();
        $cleanJson = preg_replace('/^\xEF\xBB\xBF/', '', $rawBody);
        $data = json_decode($cleanJson, true);

        // Step 4: Save Booking Data
        $datas = [
            "product_id" => $request->input('service_id'),
            "branch_id" => $request->input('branch_id') ?? 0,
            "staff_id" => $request->input('staff_id') ?? 0,
            "slot_id" => $request->input(key: 'slot_id') ?? 0,
            "booking_date"  => now()->format('Y-m-d'),
            "from_time"     => $request->input('from_time'),
            "to_time"       => $request->input('to_time'),
            "booking_status" => 1,
            "amount_tax" => $request->input('tax_amount'),
            "user_id" => $request->input('user_id'),
            "first_name" => $request->input('first_name'),
            "last_name" => $request->input('last_name'),
            "user_email" => $request->input('email'),
            "user_phone" => $request->input('phone_number'),
            "user_city" => $request->input('city'),
            "user_state" => $request->input('state'),
            "user_address" => $request->input('address'),
            "notes" => $request->input('note'),
            "user_postal" => $request->input('postal'),
            "payment_type" => 10,
            "payment_status" => 1,
            "service_qty" => 1,
            "service_amount" => $request->input('sub_amount'),
            "total_amount" => $request->input('total_amount')
        ];

        $save = Bookings::create($datas);

        if ($save) {
            $orderId = getBookingOrderId($save->id);
            $save->update(['order_id' => $orderId]);
        }

        // Step 5: Check payment response
        if (
            isset($data['transactionResponse']['responseCode']) &&
            $data['transactionResponse']['responseCode'] == '1'
        ) {
            sendBookingNotification($save->id);

            return response()->json([
                'code' => 200,
                'message' => 'Booking successfully created!',
                'data' => ['order_id' => $save->order_id]
            ]);
        }

        // Transaction failed — return detailed error
        return response()->json([
            'code' => 500,
            'message' => 'Authorize.Net is currently unavailable. Please try again later.',
            'details' => $data['transactionResponse'] ?? $data
        ], 500);
    }
}
