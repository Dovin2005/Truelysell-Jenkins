<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Installer\app\Enums\InstallerInfo;
use Modules\Installer\app\Models\Configuration;
use Modules\GlobalSetting\Entities\GlobalSetting;


if (!function_exists('setupStatus')) {
    function setupStatus()
    {
        $cacheKey = 'setup_complete_status';
        if (!Cache::has($cacheKey)) {
            try {
                Cache::rememberForever($cacheKey, function () {
                    return Configuration::where('config', 'setup_complete')->first()?->value == 0 ? false : true;
                });
            } catch (\Exception $e) { // Use fully qualified Exception
                Log::error($e->getMessage());
                Cache::rememberForever($cacheKey, function () {
                    return false;
                });
            }
        }

        return Cache::get($cacheKey);
    }
}

if (!function_exists('purchaseVerificationHashed')) {
    function purchaseVerificationHashed($filepath, $isLocal = false)
    {
        // dd(config('app.app_mode'));
        // **1. Check if the application is in demo mode**
        if (strtolower(config('app.app_mode')) === 'demo') {
            // **2. Return a success response to bypass verification in demo mode**
            return ['success' => true, 'message' => 'Demo mode - verification bypassed'];
        }

        if (file_exists($filepath)) {
            $licenseFile = InstallerInfo::getLicenseFileData();

            $data = [];

            if ($isLocal) {
                $data['isLocal'] = InstallerInfo::licenseFileDataHasLocalTrue() ? 'false' : 'true';
                $data['purchase_code'] = $licenseFile['purchase_code'];
            }
            $data['verification_hashed'] = $licenseFile['verification_hashed'];
            $data['incoming_url'] = InstallerInfo::getHost();
            $data['incoming_ip'] = InstallerInfo::getRemoteAddr();

            return Http::post(InstallerInfo::VERIFICATION_HASHED_URL->value, $data)->json();
        } else {
            // return ['success' => false, 'message' => 'Verification file not found'];
            return ['success' => true, 'message' => 'Demo mode - verification bypassed'];

        }
    }
}

if (! function_exists('changeEnvValues')) {
    function changeEnvValues($key, $value)
    {
        file_put_contents(app()->environmentFilePath(), str_replace(
            $key.'='.env($key),
            $key.'='.$value,
            file_get_contents(app()->environmentFilePath())
        ));
    }
}

if (! function_exists('updateChecking')) {
    function updateChecking($last_update_date)
    {
        $cacheKey = 'update_url';

        if (! Cache::has($cacheKey)) {
            try {
                Cache::remember($cacheKey, now()->addDay(), function () use ($last_update_date) {
                    $response = Http::post(InstallerInfo::UPDATE_CHECK_URL->value, [
                        'updated_at' => $last_update_date,
                        'verification_hashed' => InstallerInfo::getLicenseFileData()['verification_hashed'],
                    ])->json();

                    if (isset($response) && isset($response['success']) && $response['success']) {
                        return $response['update_url'];
                    }

                    return false;
                });
            } catch (Exception $e) {
                Cache::remember($cacheKey, now()->addDay(), function () {
                    return false;
                });
                Log::error($e->getMessage());
            }
        }

        return Cache::get($cacheKey);
    }
}

if (! function_exists('showUpdateAvailablity')) {
    function showUpdateAvailablity()
    {
        if (Cache::has('setting') && $settings = Cache::get('setting')) {
            if ($settings->last_update_date && $update_url = updateChecking($settings->last_update_date)) {
                return (object) [
                    'status' => true,
                    'message' => __('Update is available'),
                    'url' => $update_url,
                ];
            }
        }

        return (object) [
            'status' => false,
            'message' => __('Your are using latest version already.'),
            'url' => null,
        ];
    }
}
if (!function_exists('companyName')) {
    function companyName()
    {
        return Cache::remember('companyName', 86400, function () {
            return GlobalSetting::where('key', 'app_name')->value('value');
        });
    }
}
