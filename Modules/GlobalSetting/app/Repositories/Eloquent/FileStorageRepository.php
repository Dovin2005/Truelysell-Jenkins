<?php

namespace Modules\GlobalSetting\app\Repositories\Eloquent;

use Modules\GlobalSetting\app\Repositories\Contracts\FileStorageInterface;
use Modules\GlobalSetting\Entities\GlobalSetting;

class FileStorageRepository implements FileStorageInterface
{
    public function getSettingsByGroup(int $groupId)
    {
        return GlobalSetting::select('key', 'value', 'group_id')
            ->where('group_id', $groupId)
            ->get();
    }

    public function updateAwsSettings(array $data)
    {
        $settings = [
            'aws_access_key' => $data['aws_access_key'],
            'aws_secret_access_key' => $data['aws_secret_access_key'],
            'aws_region' => $data['aws_region'],
            'aws_bucket' => $data['aws_bucket'],
            'aws_url' => $data['aws_url'],
        ];

        foreach ($settings as $key => $value) {
            GlobalSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'group_id' => 20]
            );
        }

        return true;
    }

    public function setLocalStatus(int $status)
    {
        GlobalSetting::updateOrCreate(
            ['key' => 'local_status'],
            ['value' => $status, 'group_id' => 20]
        );

        // Set AWS status to opposite
        GlobalSetting::updateOrCreate(
            ['key' => 'aws_status'],
            ['value' => $status == 1 ? 0 : 1, 'group_id' => 20]
        );

        return $status;
    }

    public function setAwsStatus(int $status)
    {
        GlobalSetting::updateOrCreate(
            ['key' => 'aws_status'],
            ['value' => $status, 'group_id' => 20]
        );

        // Set Local status to opposite
        GlobalSetting::updateOrCreate(
            ['key' => 'local_status'],
            ['value' => $status == 1 ? 0 : 1, 'group_id' => 20]
        );

        return $status;
    }
    public function updateStorageStatus($awsStatus = null, $localStatus = null): void
    {
        if (!is_null($awsStatus)) {
            GlobalSetting::updateOrCreate(
                ['key' => 'aws_status'],
                ['value' => $awsStatus, 'group_id' => 20]
            );

            // Optional: Toggle local if awsStatus is set
            if (is_null($localStatus)) {
                GlobalSetting::updateOrCreate(
                    ['key' => 'local_status'],
                    ['value' => $awsStatus == 1 ? 0 : 1, 'group_id' => 20]
                );
            }
        }

        if (!is_null($localStatus)) {
            GlobalSetting::updateOrCreate(
                ['key' => 'local_status'],
                ['value' => $localStatus, 'group_id' => 20]
            );

            // Optional: Toggle aws if localStatus is set and awsStatus is null
            if (is_null($awsStatus)) {
                GlobalSetting::updateOrCreate(
                    ['key' => 'aws_status'],
                    ['value' => $localStatus == 1 ? 0 : 1, 'group_id' => 20]
                );
            }
        }
    }

}