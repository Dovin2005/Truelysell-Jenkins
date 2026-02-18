<?php

namespace Modules\Communication\app\Repositories\Contracts;

use Illuminate\Http\Request;

interface NotificationInterface
{
    public function saveToken(array $data): array;
    public function storeNotification(array $data): array;
    public function sendPushNotification(string $token, string $title, string $body): array;
    public function getNotificationList(array $params): array;
    public function updateReadStatus(int $userId): array;
    public function getNotificationCount(int $userId): array;
}