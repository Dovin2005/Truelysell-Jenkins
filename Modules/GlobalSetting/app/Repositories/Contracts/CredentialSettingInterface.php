<?php

namespace Modules\GlobalSetting\app\Repositories\Contracts;

interface CredentialSettingInterface
{
    public function getSettingsByGroup(int $groupId);
    public function updateOrCreateSetting(string $key, string $value, int $groupId);
    public function updateStatus(string $key, int $status, int $groupId);
    public function updateEnvVariables(array $envData);
}