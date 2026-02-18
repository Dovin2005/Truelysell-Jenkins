<?php

namespace Modules\GlobalSetting\app\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Modules\GlobalSetting\app\Models\CommunicationSettings;
use Modules\GlobalSetting\app\Models\Templates;

interface CommunicationSettingsInterface
{
    public function getDefaultSettings(): Collection;
    public function getSettingsByType(string $type): Collection;
    public function getSettingsList(?int $settingsType, string $type): Collection;
    public function setDefaultSetting(?int $id, int $enabled): bool;
    public function updateOrCreateSetting(string $key, string|int $value, int $settingsType, string $type): CommunicationSettings;
    public function updateStatus(string $type, int $status, int $settingsType): CommunicationSettings;
    public function getTemplates(array $filters = []): Collection;
    public function createTemplate(array $data): Templates;
    public function updateTemplate(?int $id, array $data): Templates;
    public function deleteTemplate(?int $id): bool;
}