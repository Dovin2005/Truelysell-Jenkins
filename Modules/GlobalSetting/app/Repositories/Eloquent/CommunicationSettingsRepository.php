<?php

namespace Modules\GlobalSetting\app\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Collection;
use Modules\GlobalSetting\app\Repositories\Contracts\CommunicationSettingsInterface;
use Modules\GlobalSetting\app\Models\CommunicationSettings;
use Modules\GlobalSetting\app\Models\Templates;
use Illuminate\Support\Facades\Log;

class CommunicationSettingsRepository implements CommunicationSettingsInterface
{
    public function getDefaultSettings(): Collection
    {
        return CommunicationSettings::where('is_default', 1)
            ->whereNull('deleted_at')
            ->get();
    }

    public function getSettingsByType(string $type): Collection
    {
        return CommunicationSettings::select('key', 'value', 'type')
            ->where('settings_type', $type)
            ->get();
    }

    public function getSettingsList(?int $settingsType, string $type): Collection
    {
        return CommunicationSettings::where('settings_type', $settingsType)
            ->where('type', $type)
            ->whereNull('deleted_at')
            ->get();
    }

    public function setDefaultSetting(?int $id, int $enabled): bool
    {
        try {
            CommunicationSettings::where('is_default', 1)->update(['is_default' => 0]);
            CommunicationSettings::where('id', $id)->update(['is_default' => $enabled]);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to set default setting: ' . $e->getMessage());
            return false;
        }
    }

    public function updateOrCreateSetting(string $key, string|int $value, int $settingsType, string $type): CommunicationSettings
    {
        return CommunicationSettings::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'settings_type' => $settingsType, 'type' => $type]
        );
    }

    public function updateStatus(string $type, int $status, int $settingsType): CommunicationSettings
    {
        $setting = CommunicationSettings::updateOrCreate(
            ['key' => $type.'_status'],
            ['value' => $status, 'settings_type' => $settingsType, 'type' => $type]
        );

        if ($status == 1) {
            CommunicationSettings::where('settings_type', $settingsType)
                ->where('type', '!=', $type)
                ->where('key', 'LIKE', '%status%')
                ->update(['value' => 0]);
        }

        return $setting;
    }

    public function getTemplates(array $filters = []): Collection
    {
        $query = Templates::query()
            ->whereNull('templates.deleted_at')
            ->join('notification_types', 'templates.notification_type', '=', 'notification_types.id')
            ->select('templates.*', 'notification_types.type as notification_type_name');

        if (!empty($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('notification_types.type', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('title', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query->orderBy($filters['sort_by'] ?? 'id', $filters['order_by'] ?? 'desc')
            ->get();
    }

    public function createTemplate(array $data): Templates
    {
        return Templates::create($data);
    }

    public function updateTemplate(?int $id, array $data): Templates
    {
        $template = Templates::findOrFail($id);
        $template->update($data);
        return $template;
    }

    public function deleteTemplate(?int $id): bool
    {
        return Templates::where('id', $id)->update(['deleted_at' => now()]);
    }
}