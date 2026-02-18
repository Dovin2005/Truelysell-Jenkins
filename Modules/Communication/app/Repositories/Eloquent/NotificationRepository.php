<?php

namespace Modules\Communication\app\Repositories\Eloquent;

use Modules\Communication\app\Repositories\Contracts\NotificationInterface;
use Modules\Communication\app\Models\Notifications;
use App\Models\User;
use Modules\GlobalSetting\Entities\GlobalSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use GuzzleHttp\Client;
use Firebase\JWT\JWT;

class NotificationRepository implements NotificationInterface
{
    public function saveToken(array $data): array
    {
        $userId = $this->getUserId($data['type']);
        User::where('id', $userId)->update(['fcm_token' => $data['token']]);
        
        return ['code' => 200, 'message' => 'FCM Token Saved successfully'];
    }

    public function storeNotification(array $data): array
    {
        $notificationData = [
            'communication_type' => $data['communication_type'],
            'source' => $data['source'],
            'reference_id' => $data['reference_id'],
            'user_id' => $data['user_id'],
            'to_user_id' => $data['to_user_id'],
            'notification_date' => date('Y-m-d'),
            'from_description' => $data['from_description'],
            'to_description' => $data['to_description'] ?? $data['description'],
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $data['user_id'],
        ];

        Notifications::create($notificationData);

        $this->sendNotifications($data);

        return ['code' => 200, 'message' => 'Notification sent successfully'];
    }

    public function sendPushNotification(string $token, string $title, string $body): array
    {
        try {
            $clientEmail = "firebase-adminsdk-70jk4@truelysell-570e9.iam.gserviceaccount.com";
            $privateKey = "-----BEGIN PRIVATE KEY-----\nMIIEvQ..."; // Your private key

            $jwt = JWT::encode([
                'iss' => $clientEmail,
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud' => 'https://oauth2.googleapis.com/token',
                'exp' => time() + 3600,
                'iat' => time(),
            ], $privateKey, 'RS256');

            $client = new Client();
            $authResponse = $client->post('https://oauth2.googleapis.com/token', [
                'form_params' => [
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                    'assertion' => $jwt,
                ],
            ]);

            $accessToken = json_decode($authResponse->getBody(), true)['access_token'];

            $response = $client->post('https://fcm.googleapis.com/v1/projects/truelysell-570e9/messages:send', [
                'json' => [
                    'message' => [
                        'token' => $token,
                        'notification' => ['title' => $title, 'body' => $body],
                        'data' => ['custom_key' => 'custom_value'],
                    ]
                ],
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ],
            ]);

            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function getNotificationList(array $params): array
    {
        $dateFormat = GlobalSetting::where('key', 'date_format_view')->value('value') ?? '%d-%m-%Y';
        $timeFormat = GlobalSetting::where('key', 'time_format_view')->value('value') ?? ' %H:%i:%s';
        $sqlDateFormat = $this->mapDateFormatToSQL($dateFormat);
        $sqlTimeFormat = $this->mapDateFormatToSQL($timeFormat);

        $userId = $params['authid'] ?? auth()->id();
        $type = $params['type'] ?? $this->getUserType($userId);

        $query = Notifications::with(['fromUser', 'toUser'])
            ->select('notifications.*')
            ->addSelect([
                'notificationdate' => DB::raw("CASE
                    WHEN DATE_FORMAT(notifications.created_at, '%H:%i:%s') = '00:00:00'
                    THEN DATE_FORMAT(notifications.created_at, '{$sqlDateFormat}')
                    ELSE DATE_FORMAT(notifications.created_at, '{$sqlDateFormat} {$sqlTimeFormat}')
                END")
            ]);

        // Add additional query logic based on type
        if ($type === 'user') {
            $query->whereNotNull('from_description')->where('from_description', '!=', '')->paginate(5);
        } elseif (in_array($type, ['provider', 'admin'])) {
            $query->paginate(8);
        } else {
            $query->where(function($q) use ($userId) {
                $q->where('user_id', $userId)->where('from_read_type', 0)
                  ->orWhere('to_user_id', $userId)->where('to_read_type', 0);
            })->take(10)->get();
        }

        return [
            'notifications' => $query,
            'count' => $this->getNotificationCount($userId)['unreadNotificationCount'],
            'auth_user' => $userId
        ];
    }

    public function updateReadStatus(int $userId): array
    {
        Notifications::where(function($query) use ($userId) {
            $query->where('user_id', $userId)->where('from_read_type', 0)
                  ->orWhere('to_user_id', $userId)->where('to_read_type', 0);
        })->update([
            'from_read_type' => DB::raw("IF(user_id = {$userId}, 1, from_read_type)"),
            'to_read_type' => DB::raw("IF(to_user_id = {$userId}, 1, to_read_type)"),
        ]);

        return ['code' => 200, 'message' => 'Notification Read status updated successfully'];
    }

    public function getNotificationCount(int $userId): array
    {
        $count = Notifications::where(function($query) use ($userId) {
            $query->where('user_id', $userId)->where('from_read_type', 0)
                  ->orWhere('to_user_id', $userId)->where('to_read_type', 0);
        })->count();

        return ['unreadNotificationCount' => $count];
    }

    private function getUserId(string $type): int
    {
        if ($type === "/provider/dashboard") {
            return auth()->id() ?? Cache::get('provider_auth_id');
        } elseif ($type === "/admin/dashboard") {
            return auth()->id();
        }
        return auth()->id() ?? Cache::get('user_auth_id');
    }

    private function getUserType(int $userId): string
    {
        return User::where('id', $userId)->value('user_type') === 3 ? 'user' : 'provider';
    }

    private function mapDateFormatToSQL(string $format): string
    {
        $replacements = [
            'd' => '%d', 'D' => '%a', 'j' => '%e', 'l' => '%W',
            'F' => '%M', 'm' => '%m', 'M' => '%b', 'n' => '%c',
            'Y' => '%Y', 'y' => '%y',
            'a' => '%p', 'A' => '%p', 'g' => '%l', 'G' => '%k',
            'h' => '%I', 'H' => '%H', 'i' => '%i', 's' => '%S',
        ];

        return strtr($format, $replacements);
    }

    private function sendNotifications(array $data): void
    {
        $fromToken = User::where('id', $data['user_id'])->value('fcm_token');
        $toToken = User::where('id', $data['to_user_id'])->value('fcm_token');

        if ($fromToken) {
            $this->sendPushNotification($fromToken, $data['source'], $data['from_description']);
        }
        if ($toToken) {
            $this->sendPushNotification($toToken, $data['source'], $data['to_description']);
        }
    }
}