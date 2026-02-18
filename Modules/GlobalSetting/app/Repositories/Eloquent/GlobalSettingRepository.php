<?php

namespace Modules\GlobalSetting\app\Repositories\Eloquent;

use Illuminate\Support\Facades\Storage;
use Modules\GlobalSetting\app\Models\Language;
use Modules\GlobalSetting\Entities\GlobalSetting;
use Modules\GlobalSetting\app\Repositories\Contracts\GlobalSettingInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;


class GlobalSettingRepository implements GlobalSettingInterface
{
    public function __construct(protected GlobalSetting $model) {}

    public function index(array $filters = [])
    {
        return $this->model->filter($filters)->get();
    }

    public function getByGroup(int $groupId)
    {
        $query = $this->model->where('group_id', $groupId);

        if ($groupId === 1) {
            $query->orWhere('key', 'sso_status');
        }

        $settings = $query->get();

        return $settings->map(function ($setting) {
            if ($setting->key === 'invoice_company_logo') {
                $setting->value = $this->model->file($setting->value);
            }
            return $setting;
        });
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data)
    {
        $setting = $this->model->findOrFail($id);
        $setting->update($data);
        return $setting;
    }

    public function delete(int $id)
    {
        $setting = $this->model->findOrFail($id);
        $setting->delete();
        return true;
    }

    public function updateGeneralSettings(array $data)
    {
        DB::beginTransaction();
        try {
            if (!array_key_exists("save_single_vendor_status", $data)) {
                $this->model->updateOrCreate(
                    ['key' => 'save_single_vendor_status'],
                    [
                        'value' => 'off',
                        'group_id' => $data['group_id']
                    ]
                );
            }

            if (isset($data['sso_status'])) {
                $this->model->updateOrCreate(
                    ['key' => 'sso_status'],
                    [
                        'value' => $data['sso_status'],
                        'group_id' => 4
                    ]
                );
            }

            foreach ($data as $key => $value) {
                if ($key != 'group_id') {
                    $this->model->updateOrCreate(
                        ['key' => $key],
                        [
                            'value' => $value,
                            'group_id' => $data['group_id']
                        ]
                    );
                }
            }

            Cache::forget('singlevendor');
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updatePaymentSettings(array $data)
    {
        DB::beginTransaction();
        try {
            $groupId = $data['group_id'] ?? 13;

            foreach ($data as $key => $value) {
                if ($key !== 'group_id' && $value) {
                    $this->model->updateOrCreate(
                        ['key' => $key],
                        [
                            'value' => $value,
                            'group_id' => $groupId
                        ]
                    );
                }
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateInvoiceSettings(array $data)
    {
        DB::beginTransaction();
        try {
            if ( $data['invoice_logo'] != "undefined" && isset($data['invoice_logo']) && $data['invoice_logo']->isValid()) {
                $path = $data['invoice_logo']->store('invoice-logos', 'public');
                $this->model->updateOrCreate(
                    ['key' => 'invoice_company_logo'],
                    [
                        'value' => $path,
                        'group_id' => $data['group_id']
                    ]
                );
            }

            foreach ($data as $key => $value) {
                if ($key != 'group_id' && $key != 'invoice_logo') {
                    $this->model->updateOrCreate(
                        ['key' => $key],
                        [
                            'value' => $value,
                            'group_id' => $data['group_id']
                        ]
                    );
                }
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateEnvVariables(array $data)
    {
        $path = base_path('.env');
        if (!File::exists($path)) {
            return false;
        }

        $envContent = File::get($path);
        $updated = false;

        foreach ($data as $key => $value) {
            $pattern = "/^{$key}=.*/m";
            if (preg_match($pattern, $envContent)) {
                $envContent = preg_replace($pattern, "{$key}={$value}", $envContent);
                $updated = true;
            } else {
                $envContent .= "\n{$key}={$value}";
                $updated = true;
            }
        }

        if ($updated) {
            return File::put($path, $envContent) !== false;
        }

        return false;
    }

    public function getSettingByKey(string $key)
    {
        return $this->model->where('key', $key)->first();
    }
    public function updateOrCreate(array $attributes, array $values)
    {
        return $this->model->updateOrCreate($attributes, $values);
    }

    public function deleteByKey(string $key)
    {
        return $this->model->where('key', $key)->delete();
    }

    public function getFile(string $path)
    {
        return Storage::url($path);
    }

    public function updateTaxStatus(string $key, string $status)
    {
        return $this->model->where('key', $key)->update(['value' => $status]);
    }

    public function updateMultiple(array $settings, int $groupId, ?int $languageId = null)
    {
        foreach ($settings as $key => $value) {
            $attributes = ['key' => $key];

            if ($languageId) {
                $attributes['language_id'] = $languageId;
            }

            $dataToUpdate = [
                'value' => $value ?? "",
                'group_id' => $groupId,
            ];

            if ($key != "group_id") {
                $this->model->updateOrCreate($attributes, $dataToUpdate);
            }
        }
    }

     public function updateLogoSettings(array $data)
    {
        try {
            $settings = collect($data)->except(['_token', 'logo', 'favicon', 'icon', 'dark_logo', 'mobile_icon'])->toArray();

            if (isset($data['logo'])) {
                $this->handleLogoUpdate($data['logo'], 'site_logo', $data['group_id']);
            }

            if (isset($data['favicon'])) {
                $this->handleLogoUpdate($data['favicon'], 'site_favicon', $data['group_id']);
            }

            if (isset($data['icon'])) {
                $this->handleLogoUpdate($data['icon'], 'site_icon', $data['group_id']);
            }

            if (isset($data['mobile_icon'])) {
                $this->handleLogoUpdate($data['mobile_icon'], 'site_mobile_icon', $data['group_id']);
            }

            if (isset($data['dark_logo'])) {
                $this->handleLogoUpdate($data['dark_logo'], 'site_dark_logo', $data['group_id']);
            }

            $this->clearLogoCache();

            return [
                'success' => true,
                'message' => __('Global setting updated successfully.')
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error updating logo settings: ' . $e->getMessage()
            ];
        }
    }

    public function getLogoSettings(int $groupId)
    {
        $settings = GlobalSetting::where('group_id', $groupId)->get();

        return $settings->map(function ($setting) {
            if (in_array($setting->key, ['site_logo', 'site_favicon', 'site_icon', 'site_dark_logo', 'site_mobile_icon'])) {
                $setting->value = $this->getFileUrl($setting->value);
            }
            return $setting;
        });
    }

    public function updateCustomSettings(array $data)
    {
        try {
            $settings = collect($data)->except(['_token'])->toArray();

            foreach ($settings as $key => $value) {
                $this->updateOrCreateSetting($key, [
                    'value' => $value ?? '',
                    'group_id' => $data['group_id']
                ]);
            }

            // Handle custom CSS/JS files if present
            if (isset($data['custom_setting_content'])) {
                $cssFilePath = public_path('assets/css/custom.css');
                file_put_contents($cssFilePath, $data['custom_setting_content']);
            }

            if (isset($data['custom_setting_content1'])) {
                $jsFilePath = public_path('assets/js/custom.js');
                file_put_contents($jsFilePath, $data['custom_setting_content1']);
            }

            return [
                'success' => true,
                'message' => __('Global setting updated successfully.')
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error updating custom settings: ' . $e->getMessage()
            ];
        }
    }

    public function getCustomSettings(int $groupId)
    {
        $settings = GlobalSetting::where('group_id', $groupId)->get();

        return $settings->map(function ($setting) {
            if ($setting->key === 'invoice_company_logo') {
                $setting->value = $this->getFileUrl($setting->value);
            }
            return $setting;
        });
    }

    public function updateOrCreateSetting(string $key, array $data)
    {
        return GlobalSetting::updateOrCreate(
            ['key' => $key],
            $data
        );
    }

    public function deleteFileIfExists(string $filePath)
    {
        if ($filePath && Storage::disk('public')->exists($filePath)) {
            Storage::disk('public')->delete($filePath);
        }
    }

    public function storeFile($file, string $directory): string
    {
        return $file->store($directory, 'public');
    }

    protected function handleLogoUpdate($file, string $key, int $groupId)
    {
        $oldSetting = GlobalSetting::where('key', $key)->first();
        
        if ($oldSetting && $oldSetting->value) {
            $this->deleteFileIfExists($oldSetting->value);
        }

        $path = $this->storeFile($file, $this->getDirectoryForKey($key));

        $this->updateOrCreateSetting($key, [
            'value' => $path,
            'group_id' => $groupId
        ]);
    }

    protected function getDirectoryForKey(string $key): string
    {
        $directories = [
            'site_logo' => 'logos',
            'site_favicon' => 'favicons',
            'site_icon' => 'icons',
            'site_mobile_icon' => 'mobile-icons',
            'site_dark_logo' => 'dark-logos',
        ];

        return $directories[$key] ?? 'uploads';
    }

    protected function getFileUrl(?string $path): ?string
    {
        return $path ? Storage::disk('public')->url($path) : null;
    }

    protected function clearLogoCache()
    {
        Cache::forget('logoPath');
        Cache::forget('faviconPath');
        Cache::forget('darkLogoPath');
        Cache::forget('smallLogoPath');
        Cache::forget('iconMobilePath');
    }

    public function updateCopyrightSettings(array $data)
    {
        try {
            $language = Language::select('id', 'code')->findOrFail($data['language_id']);
            $key = 'copyright_' . $language->code;

            $this->updateOrCreateSettingWithLanguage(
                [
                    'group_id' => $data['group_id'],
                    'language_id' => $data['language_id'],
                    'key' => $key,
                ],
                ['value' => $data['copyright']]
            );

            Cache::forget('copyRight_' . $data['language_id']);

            return [
                'success' => true,
                'message' => __('Copyright setting updated successfully.'),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error updating copyright settings: ' . $e->getMessage(),
            ];
        }
    }

    public function updateCookiesSettings(array $data)
    {
        try {
            $language = Language::select('id', 'code')->findOrFail($data['language_id']);
            $keys = [
                'cookies_content_text',
                'cookies_position',
                'agree_button_text',
                'decline_button_text',
                'show_decline_button',
                'lin_for_cookies_page'
            ];

            foreach ($keys as $key) {
                $dynamicKey = $key . '_' . $language->code;
                $value = $data[$key] ?? null;

                $this->updateOrCreateSettingWithLanguage(
                    [
                        'group_id' => $data['group_id'],
                        'key' => $dynamicKey,
                        'language_id' => $data['language_id'],
                    ],
                    ['value' => $value]
                );
            }

            return [
                'success' => true,
                'message' => __('Cookies settings updated successfully.')
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error updating cookies settings: ' . $e->getMessage()
            ];
        }
    }

    public function updateOrCreateSettingWithLanguage(array $conditions, array $data)
    {
        return GlobalSetting::updateOrCreate($conditions, $data);
    }


}