<?php

namespace Modules\GlobalSetting\app\Repositories\Contracts;

interface GlobalSettingInterface
{
    public function index(array $filters = []);
    public function getByGroup(int $groupId);
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);
    public function updateGeneralSettings(array $data);
    public function updatePaymentSettings(array $data);
    public function updateInvoiceSettings(array $data);
    public function updateEnvVariables(array $data);
    public function getSettingByKey(string $key);
    public function updateOrCreate(array $attributes, array $values);
    public function deleteByKey(string $key);
    public function getFile(string $path);
    public function updateTaxStatus(string $key, string $status);
    public function updateMultiple(array $settings, int $groupId, ?int $languageId = null);

    public function updateLogoSettings(array $data);
    public function getLogoSettings(int $groupId);
    public function updateCustomSettings(array $data);
    public function getCustomSettings(int $groupId);
    public function updateOrCreateSetting(string $key, array $data);
    public function deleteFileIfExists(string $filePath);
    public function storeFile($file, string $directory): string;
    public function updateCopyrightSettings(array $data);
    public function updateCookiesSettings(array $data);
    public function updateOrCreateSettingWithLanguage(array $conditions, array $data);
}