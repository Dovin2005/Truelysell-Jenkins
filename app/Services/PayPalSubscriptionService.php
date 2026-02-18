<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayPalSubscriptionService
{
    protected $baseUrl;
    protected $clientId;
    protected $secret;

    public function __construct()
    {
        $this->baseUrl = env('PAYPAL_MODE') === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';

        $this->clientId = env('PAYPAL_SANDBOX_CLIENT_ID');
        $this->secret = env('PAYPAL_SANDBOX_CLIENT_SECRET');
    }

    public function getAccessToken()
    {
        $response = Http::asForm()
            ->withBasicAuth($this->clientId, $this->secret)
            ->post("{$this->baseUrl}/v1/oauth2/token", [
                'grant_type' => 'client_credentials',
            ]);

        if (!$response->successful()) {
            throw new \Exception('Failed to get PayPal access token: ' . $response->body());
        }
        return $response->json()['access_token'];
    }

    public function createProduct(string $name, string $description = '')
    {
        $token = $this->getAccessToken();

        $response = Http::withToken($token)->post("{$this->baseUrl}/v1/catalogs/products", [
            'name' => $name,
            'description' => $description ?: $name,
            'type' => 'SERVICE',
            'category' => 'SOFTWARE',
        ]);

        if (!$response->successful()) {
            throw new \Exception('Product creation failed: ' . $response->body());
        }

        return $response->json();
    }

    public function createPlan(string $productId, string $name, float $price, string $interval = 'MONTH', $intervalCount = 1)
    {
        $token = $this->getAccessToken();
        $currencyCode = strtoupper(getDefaultCurrencyCode());

        $response = Http::withToken($token)->post("{$this->baseUrl}/v1/billing/plans", [
            'product_id' => $productId,
            'name' => $name,
            'billing_cycles' => [[
                'frequency' => [
                    'interval_unit' => strtoupper($interval),
                    'interval_count' => $intervalCount,
                ],
                'tenure_type' => 'REGULAR',
                'sequence' => 1,
                'total_cycles' => 0,
                'pricing_scheme' => [
                    'fixed_price' => [
                        'value' => number_format($price, 2, '.', ''),
                        'currency_code' => $currencyCode,
                    ],
                ],
            ]],
            'payment_preferences' => [
                'auto_bill_outstanding' => true,
                'setup_fee' => [
                    'value' => '0',
                    'currency_code' => $currencyCode,
                ],
                'setup_fee_failure_action' => 'CONTINUE',
                'payment_failure_threshold' => 1,
            ],
        ]);

        if (!$response->successful()) {
            throw new \Exception('Plan creation failed: ' . $response->body());
        }

        return $response->json();
    }

    // Fetch Plans from PayPal
    public function fetchPlans()
    {
        $token = $this->getAccessToken();

        $response = Http::withToken($token)->get("{$this->baseUrl}/v1/billing/plans");

        if (!$response->successful()) {
            throw new \Exception('Failed to fetch plans: ' . $response->body());
        }

        return $response->json();
    }
    //fetchPlan
    public function fetchPlan($planId)
    {
        if ($planId) {
            $token = $this->getAccessToken();
            $response = Http::withToken($token)->get("{$this->baseUrl}/v1/billing/plans/{$planId}");
            if (!$response->successful()) {
                throw new \Exception('Failed to fetch plan: ' . $response->body());
            }
            return $response->json();
        }
    }
    //fetch products from PayPal
    public function fetchProducts()
    {
        $token = $this->getAccessToken();

        $response = Http::withToken($token)->get("{$this->baseUrl}/v1/catalogs/products");

        if (!$response->successful()) {
            throw new \Exception('Failed to fetch products: ' . $response->body());
        }

        return $response->json();
    }

    //fetch subscriptions from PayPal
    public function fetchSubscriptions()
    {
        $token = $this->getAccessToken();

        $response = Http::withToken($token)->get("{$this->baseUrl}/v1/billing/subscriptions");

        if (!$response->successful()) {
            throw new \Exception('Failed to fetch subscriptions: ' . $response->body());
        }

        return $response->json();
    }

    //fetch subscription from PayPal
    public function fetchSubscription($subscriptionId)
    {
        if ($subscriptionId) {
            $token = $this->getAccessToken();
            $response = Http::withToken($token)->get("{$this->baseUrl}/v1/billing/subscriptions/{$subscriptionId}");
            if (!$response->successful()) {
                throw new \Exception('Failed to fetch subscription: ' . $response->body());
            }
            return $response->json();
        }
    }

    // Cancel subscription directly in PayPal
    public function cancelPaypalSubscription(string $subscriptionId)
    {
        try {
            $accessToken = $this->getAccessToken();
            if (!$accessToken) {
                return false;
            }
            $endpoint = $this->baseUrl . '/v1/billing/subscriptions/' . $subscriptionId . '/cancel';
            $response = Http::withToken($accessToken)
                ->post($endpoint, [
                    'reason' => 'Cancelled automatically by webhook after payment failure or manual cancellation.',
                ]);
            if ($response->successful()) {
                Log::info("PayPal: Successfully cancelled subscription {$subscriptionId} via API");
                return true;
            } else {
                Log::error("PayPal Cancel API failed for {$subscriptionId}", [
                    'response' => $response->json(),
                ]);
                return false;
            }
        } catch (\Throwable $e) {
            Log::error("PayPal cancel error: {$e->getMessage()}");
            return false;
        }
    }
}
