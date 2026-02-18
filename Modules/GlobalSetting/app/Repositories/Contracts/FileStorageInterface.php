<?php

namespace Modules\GlobalSetting\app\Repositories\Contracts;

interface FileStorageInterface
{
    public function getSettingsByGroup(int $groupId);
    public function updateAwsSettings(array $data);
    public function setLocalStatus(int $status);
    public function setAwsStatus(int $status);
}