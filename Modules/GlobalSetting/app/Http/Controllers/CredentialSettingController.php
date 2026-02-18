<?php

namespace Modules\GlobalSetting\app\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Modules\GlobalSetting\app\Http\Requests\AnalyticsRequest;
use Modules\GlobalSetting\app\Http\Requests\ChatGPTRequest;
use Modules\GlobalSetting\app\Http\Requests\LocationRequest;
use Modules\GlobalSetting\app\Http\Requests\RecaptchaRequest;
use Modules\GlobalSetting\app\Http\Requests\SSORequest;
use Modules\GlobalSetting\app\Http\Requests\TagManagerRequest;
use Modules\GlobalSetting\app\Repositories\Contracts\CredentialSettingInterface;
use Modules\GlobalSetting\app\Http\Requests\StatusRequest;

class CredentialSettingController extends Controller
{
    protected $credentialSettingRepository;
    protected const GROUP_ID = 4;

    public function __construct(CredentialSettingInterface $credentialSettingRepository)
    {
        $this->credentialSettingRepository = $credentialSettingRepository;
    }

    public function index(Request $request): JsonResponse
    {
        $groupId = $request->input('group_id', self::GROUP_ID);
        $settings = $this->credentialSettingRepository->getSettingsByGroup($groupId);

        return response()->json([
            'code' => 200,
            'message' => __('Data retrieved successfully'),
            'data' => ['settings' => $settings]
        ], 200);
    }

    public function storeRecaptcha(RecaptchaRequest $request): JsonResponse
    {
        $this->credentialSettingRepository->updateOrCreateSetting(
            'recaptcha_api_key',
            $request->recaptcha_api_key,
            self::GROUP_ID
        );

        $this->credentialSettingRepository->updateOrCreateSetting(
            'recaptcha_secret_key',
            $request->recaptcha_secret_key,
            self::GROUP_ID
        );

        return response()->json([
            'code' => 200,
            'message' => __('credential_settings_update_success'),
            'data' => []
        ], 200);
    }

    public function statusRecaptcha(StatusRequest $request): JsonResponse
    {
        $this->credentialSettingRepository->updateStatus(
            'captcha_status',
            $request->recaptcha_status,
            self::GROUP_ID
        );

        $message = $request->status == 1 ? 'Status Updated' : 'Status Updated';
        return response()->json([
            'code' => 200,
            'message' => $message,
            'data' => []
        ], 200);
    }

    public function storeTagManager(TagManagerRequest $request): JsonResponse
    {
        $this->credentialSettingRepository->updateOrCreateSetting(
            'google_tag_id',
            $request->google_tag_id,
            self::GROUP_ID
        );

        return response()->json([
            'code' => 200,
            'message' => __('credential_settings_update_success'),
            'data' => []
        ], 200);
    }

    public function statusTagManager(StatusRequest $request): JsonResponse
    {
        $this->credentialSettingRepository->updateStatus(
            'tag_status',
            $request->tag_status,
            self::GROUP_ID
        );

        $message = $request->tag_status == 1 ? 'Status Updated' : 'Status Updated';
        return response()->json([
            'code' => 200,
            'message' => $message,
            'data' => []
        ], 200);
    }

    public function storeAnalytics(AnalyticsRequest $request): JsonResponse
    {
        $this->credentialSettingRepository->updateOrCreateSetting(
            'google_analytics_id',
            $request->google_analytics_id,
            self::GROUP_ID
        );

        return response()->json([
            'code' => 200,
            'message' => __('credential_settings_update_success'),
            'data' => []
        ], 200);
    }

    public function statusAnalytics(StatusRequest $request): JsonResponse
    {
        $this->credentialSettingRepository->updateStatus(
            'analytics_status',
            $request->analytics_status,
            self::GROUP_ID
        );

        $message = $request->analytics_status == 1 ? 'Status Updated' : 'Status Updated';
        return response()->json([
            'code' => 200,
            'message' => $message,
            'data' => []
        ], 200);
    }

    public function storeSSO(SSORequest $request): JsonResponse
    {
        try {
            $envUpdates = [
                'SSO_CLIENT_ID' => $request->sso_client_id,
                'SSO_CLIENT_SECRET' => $request->sso_client_secret,
                'SSO_REDIRECT_URL' => $request->sso_redirect_url,
            ];

            // Update .env file
            $this->credentialSettingRepository->updateEnvVariables($envUpdates);

            // Store in database
            foreach ($envUpdates as $key => $value) {
                $this->credentialSettingRepository->updateOrCreateSetting(
                    strtolower($key),
                    $value,
                    self::GROUP_ID
                );
            }

            return response()->json([
                'code' => 200,
                'message' => __('credential_settings_update_success'),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => 'Error! updating SSO settings'
            ], 500);
        }
    }

    public function statusSSO(StatusRequest $request): JsonResponse
    {
        $this->credentialSettingRepository->updateStatus(
            'sso_status',
            $request->sso_status,
            self::GROUP_ID
        );

        $message = $request->status == 1 ? 'Status Updated' : 'Status Updated';
        return response()->json([
            'code' => 200,
            'message' => $message,
            'data' => []
        ], 200);
    }

    public function storechatgpt(ChatGPTRequest $request): JsonResponse
    {
        $apiKey = $request->chatgpt_api_key ?? '';

        $this->credentialSettingRepository->updateOrCreateSetting(
            'chatgpt_api_key',
            $apiKey,
            self::GROUP_ID
        );

        $this->credentialSettingRepository->updateEnvVariables([
            'OPENAI_API_KEY' => $apiKey,
        ]);

        return response()->json([
            'code' => 200,
            'message' => __('credential_settings_update_success'),
            'data' => []
        ], 200);
    }

    public function storeLocation(LocationRequest $request): JsonResponse
    {
        $apiKey = $request->location_api_key ?? '';

        // Save API key
        $this->credentialSettingRepository->updateOrCreateSetting(
            'location_api_key',
            $apiKey,
            self::GROUP_ID
        );

        $distanceData = [];
        if ($request->filled('location_unit')) {
            $distanceData['location_unit'] = $request->location_unit;
        }

        if ($request->filled('location_distance')) { // <- fixed key
            $distanceData['location_distance_value'] = $request->location_distance;
        }

        // Store in database
        foreach ($distanceData as $key => $value) {
            $this->credentialSettingRepository->updateOrCreateSetting(
                strtolower($key),
                $value,
                self::GROUP_ID
            );
        }

        // Update .env API key
        $updated = $this->credentialSettingRepository->updateEnvVariables([
            'GOOGLE_MAPS_API_KEY' => $apiKey,
        ]);

        if (!$updated) {
            return response()->json([
                'message' => 'API key saved but failed to update .env file.'
            ], 500);
        }

        return response()->json([
            'code' => 200,
            'message' => __('credential_settings_update_success'),
            'data' => [],
        ]);
    }

    public function googlerecaptcha(RecaptchaRequest $request): JsonResponse
    {
        $this->credentialSettingRepository->updateOrCreateSetting(
            'recaptcha_api_key',
            $request->recaptcha_api_key,
            self::GROUP_ID
        );

        $this->credentialSettingRepository->updateOrCreateSetting(
            'recaptcha_secret_key',
            $request->recaptcha_secret_key,
            self::GROUP_ID
        );

        $updated = $this->credentialSettingRepository->updateEnvVariables([
            'RECAPTCHA_SITE_KEY' => $request->recaptcha_api_key,
            'RECAPTCHA_SECRET_KEY' => $request->recaptcha_secret_key,
        ]);

        if (!$updated) {
            return response()->json([
                'message' => 'Keys saved, but failed to update .env file.'
            ], 500);
        }

        return response()->json([
            'code' => 200,
            'message' => __('credential_settings_update_success'),
            'data' => [],
        ]);
    }

    public function updatechatgptstatus(StatusRequest $request): JsonResponse
    {
        $this->credentialSettingRepository->updateStatus(
            'chatgpt_status',
            $request->chatgpt_status,
            self::GROUP_ID
        );

        $message = $request->status == 1 ? 'Status Updated' : 'Status Updated';
        return response()->json([
            'code' => 200,
            'message' => $message,
            'data' => []
        ], 200);
    }

    public function updaterecaptchastatus(StatusRequest $request): JsonResponse
    {
        $this->credentialSettingRepository->updateStatus(
            'recaptcha_status',
            $request->recaptcha_status,
            self::GROUP_ID
        );

        $message = $request->recaptcha_status == 1 ? 'Status Updated' : 'Status Updated';
        return response()->json([
            'code' => 200,
            'message' => $message,
            'data' => []
        ], 200);
    }

    public function updatelocationstatus(StatusRequest $request): JsonResponse
    {
        $this->credentialSettingRepository->updateStatus(
            'location_status',
            $request->location_status,
            self::GROUP_ID
        );

        $message = $request->location_status == 1 ? 'Status Updated' : 'Status Updated';
        return response()->json([
            'code' => 200,
            'message' => $message,
            'data' => []
        ], 200);
    }
}