<?php
namespace App\Helpers;
use Illuminate\Support\Facades\Http;

class FCMNotification
{
   /**
 * Send a push notification using FCM.
 *
 * @param string $title The title of the notification.
 * @param string $body The body of the notification.
 * @param string $fcmToken The FCM token to send the notification to.
 * @return mixed The response decoded from the FCM server response.
 */
    public static function sendNotification(string $title, string $body, string $fcmToken): mixed
    {
        $response = Http::withHeaders([
            'Authorization' => 'key=' . env('FCM_SERVER_KEY'),
            'Content-Type' => 'application/json',
        ])->post('https://fcm.googleapis.com/fcm/send', [
            'to' => $fcmToken,
            'notification' => [
                'title' => $title,
                'body' => $body,
                'icon' => '/path-to-icon.png',
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
            ],
            'data' => [
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                'sound' => 'default',
                'message' => $body
            ],
        ]);

        return $response->json();
    }
}
