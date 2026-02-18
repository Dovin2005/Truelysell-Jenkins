<?php

namespace Modules\GlobalSetting\app\Repositories\Eloquent;

use Modules\GlobalSetting\app\Repositories\Contracts\CredentialSettingInterface;
use Modules\GlobalSetting\Entities\GlobalSetting;

class CredentialSettingRepository implements CredentialSettingInterface
{
    public function getSettingsByGroup(int $groupId)
    {
        return GlobalSetting::select('key', 'value', 'group_id')
            ->where('group_id', $groupId)
            ->get();
    }

    public function updateOrCreateSetting(string $key, string $value, int $groupId)
    {
        return GlobalSetting::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group_id' => $groupId]
        );
    }

    public function updateStatus(string $key, int $status, int $groupId,  ?string $unit = null, ?string $distance = null)
    {
        $data = ['status' => $status];

        if ($unit !== null) {
            $data['unit'] = $unit;
        }

        if ($distance !== null) {
            $data['distance'] = $distance;
        }

        return GlobalSetting::updateOrCreate(
            ['key' => $key],
            ['value' => $status, 'group_id' => $groupId]
        );
    }

    public function updateEnvVariables(array $envData): bool
    {
        $path = base_path('.env');
    
        if (!file_exists($path)) {
            return false;
        }
    
        $envContent = file_get_contents($path);
        if ($envContent === false) {
            return false;
        }
    
        foreach ($envData as $key => $value) {
            // Escape any existing double quotes
            $escapedValue = str_replace('"', '\"', $value);
    
            // Always wrap the value in double quotes
            $formattedValue = "{$key}=\"{$escapedValue}\"";
    
            // Use regex to match the key (with optional quotes in the value)
            $pattern = "/^{$key}=.*$/m";
    
            if (preg_match($pattern, $envContent)) {
                $envContent = preg_replace($pattern, $formattedValue, $envContent);
            } else {
                $envContent .= "\n{$formattedValue}";
            }
        }
    
        return file_put_contents($path, $envContent) !== false;
    }
}