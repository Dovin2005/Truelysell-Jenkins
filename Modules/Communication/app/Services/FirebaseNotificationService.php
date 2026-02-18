<?php

namespace Modules\Communication\app\Services;

use Google\Cloud\Core\ServiceBuilder;
/** @method static \Google\Auth\Credentials\ServiceAccountCredentials fromJsonFile(string $jsonFile) */

use Google\Auth\Credentials\ServiceAccountCredentials;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

class FirebaseNotificationService
{
    /** @var string */
    protected string $fcmUrl;
    /** @var string */
    protected string $serviceAccountPath;

    public function __construct()
    {
        // Firebase Cloud Messaging URL
        $this->fcmUrl = 'https://fcm.googleapis.com/v1/projects/truelysell-570e9/messages:send';
        
        // Service account path
        $this->serviceAccountPath = storage_path('firebase/firebase_service_account.json');
    }
/**
 * @return array<string, mixed>
 */
    public function sendNotification(string $title, string $body, string $deviceToken)
    {
        // Check if the file exists and is accessible
        if (!file_exists($this->serviceAccountPath)) {
            return ['error' => 'Service account file not found at: ' . $this->serviceAccountPath];
        }

        // Set up Google credentials using the service account JSON
        $credentials = ServiceAccountCredentials::fromJsonFile($this->serviceAccountPath);

        // Get the access token from the credentials
        $token = $credentials->fetchAuthToken();

        // Create a handler stack
        $stack = HandlerStack::create();

        // Guzzle client with handler stack
        $client = new Client(['handler' => $stack]);

        // Prepare the notification payload
        $payload = [
            'message' => [
                'token' => $deviceToken,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => [
                    'custom_key' => 'custom_value', // Optional additional data
                ],
            ],
        ];

        try {
            // Send the notification
            $response = $client->post($this->fcmUrl, [
                'json' => $payload,
                'headers' => [
                    'Authorization' => 'Bearer ' . $token['access_token'], // Use the generated token
                    'Content-Type' => 'application/json',
                ],
            ]);

            // Return the response body
            return json_decode($response->getBody(), true);

        } catch (\Exception $e) {
            // Catch and return the exception message
            return ['error' => $e->getMessage()];
        }
    }
}
