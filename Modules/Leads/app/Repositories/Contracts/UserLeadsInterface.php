<?php

namespace Modules\Leads\app\Repositories\Contracts;

interface UserLeadsInterface
{
    public function updateUserStatus(array $data): array;
    public function getStatusSummary(int $userFormInputId): array;
    public function updateProviderStatus(int $providerFormInputId, int $status);
    public function getUserFormInput(int $id);
    public function getProviderFormInput(int $id);
}