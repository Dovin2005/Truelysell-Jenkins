<?php

namespace Modules\GlobalSetting\app\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Paymentmethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\GlobalSetting\app\Http\Requests\CopyrightSettingsRequest;
use Modules\GlobalSetting\app\Http\Requests\LogoSettingsRequest;
use Modules\GlobalSetting\app\Http\Requests\PaymentSettingsRequest;
use Modules\GlobalSetting\app\Repositories\Contracts\GlobalSettingInterface;
use Modules\GlobalSetting\Entities\GlobalSetting;
use Modules\GlobalSetting\app\Http\Requests\UpdateGeneralSettingsRequest;
use Modules\GlobalSetting\app\Http\Requests\UpdateInvoiceSettingsRequest;
use Modules\GlobalSetting\app\Http\Requests\AdminGlobalSettingRequest;
use Modules\GlobalSetting\app\Http\Requests\AdminCommissionRequest;
use Modules\GlobalSetting\app\Http\Requests\TaxOptionsRequest;
use Modules\GlobalSetting\app\Http\Requests\CookiesSettingsRequest;
use Modules\GlobalSetting\app\Http\Requests\OTPSettingsRequest;
use Modules\GlobalSetting\app\Http\Requests\CustomSettingsRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;
use Modules\GlobalSetting\app\Models\Language;

use function Laravel\Prompts\select;

class GlobalSettingController extends Controller
{

protected $globalSettingRepository;

    public function __construct(GlobalSettingInterface $globalSettingRepository)
    {
        $this->globalSettingRepository = $globalSettingRepository;
    }

    public function index(Request $request): JsonResponse
    {
        $groupId = $request->input('group_id');
        $settings = $this->globalSettingRepository->getByGroup($groupId);

        $taxCount = '';
        if ($groupId == 3) {
            $taxCount = $this->globalSettingRepository->getByGroup($groupId)->count();
            $taxCount = ($taxCount / 3) + 1;
        }

        return response()->json([
            'code' => 200,
            'message' => __('Data retrieved successfully'),
            'data' => [
                'settings' => $settings,
                'taxCount' => $taxCount
            ],
        ], 200);
    }

    public function colorSettings(Request $request)
    {
        return view('globalsetting::setting.color-settings');
    }

     public function updateColorSettings(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'primary_color' => 'required|string|max:20',
            'secondary_color' => 'required|string|max:20',
            'primary_hover_color' => 'required|string|max:20',
            'secondary_hover_color' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'code' => 422,
                'message' => __('validation error'),
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $settings = $request->except('_token', 'group_id');

            foreach ($settings as $key => $value) {
                GlobalSetting::updateOrCreate(
                    ['key' => $key, 'group_id' => $request->group_id ?? 16],
                    ['value' => $value]
                );
            }

            return response()->json([
                'status' => 'success',
                'code' => 200,
                'message' => __('color_settings_update_success')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'code' => 500,
                'message' => __('color_settings_update_error'),
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function store(AdminGlobalSettingRequest $request): JsonResponse
    {
        try {
            $existingSetting = $this->globalSettingRepository->getSettingByKey($request->input('key'));
            if ($existingSetting) {
                return response()->json([
                    'code' => 409,
                    'message' => __('Setting with this key already exists.'),
                ], 409);
            }

            $setting = $this->globalSettingRepository->create($request->all());

            return response()->json([
                'code' => 201,
                'message' => __('Global setting created successfully.'),
                'data' => $setting
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => 'Error creating global setting: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(AdminGlobalSettingRequest $request, $id): JsonResponse
    {
        try {
            $setting = $this->globalSettingRepository->update($id, $request->all());

            return response()->json([
                'code' => 200,
                'message' => __('Global setting updated successfully.'),
                'data' => $setting
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'code' => 404,
                'message' => __('Global setting not found.'),
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => 'Error updating global setting: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $this->globalSettingRepository->delete($id);

            return response()->json([
                'code' => 200,
                'message' => __('Global setting deleted successfully.'),
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'code' => 404,
                'message' => __('Global setting not found.'),
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => 'Error deleting global setting: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateGeneralSettings(UpdateGeneralSettingsRequest $request): JsonResponse
    {
        try {
            $data = $request->all();
            $this->globalSettingRepository->updateGeneralSettings($data);
            Cache::forget('singlevendor');
            clearCache();
            return response()->json([
                'code' => 200,
                'message' => __('Global setting updated successfully.')
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => 'Error updating global settings: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updatepaymentSettings(PaymentSettingsRequest $request): JsonResponse
    {
        try {
            $data = $request->all();
            $this->globalSettingRepository->updatePaymentSettings($data);

            // Update payment method statuses
            if ($request->has('paypal_status')) {
                Paymentmethod::where('label', 'paypal')->update(['status' => $request->paypal_status]);
            }
            if ($request->has('stripe_status')) {
                Paymentmethod::where('label', 'stripe')->update(['status' => $request->stripe_status]);
            }
            if ($request->has('bank_status')) {
                Paymentmethod::where('label', 'banktransfer')->update(['status' => $request->bank_status]);
            }
            if ($request->has('wallet_status')) {
                Paymentmethod::where('label', 'wallet')->update(['status' => $request->wallet_status]);
            }

            // Update environment variables
            $envUpdates = [];
            if (isset($data['paypal_id'])) $envUpdates['PAYPAL_SANDBOX_CLIENT_ID'] = $data['paypal_id'];
            if (isset($data['paypal_secret'])) $envUpdates['PAYPAL_SANDBOX_CLIENT_SECRET'] = $data['paypal_secret'];
            if (isset($data['paypal_live'])) $envUpdates['PAYPAL_MODE'] = ($data['paypal_live'] == 1) ? 'sandbox' : 'live';
            if (isset($data['stripe_key'])) $envUpdates['STRIPE_KEY'] = $data['stripe_key'];
            if (isset($data['stripe_secret'])) $envUpdates['STRIPE_SECRET'] = $data['stripe_secret'];
            if (isset($data['stripe_webhook_secret'])) $envUpdates['STRIPE_WEBHOOK_SECRET'] = $data['stripe_webhook_secret'];

            if (!empty($envUpdates)) {
                $this->globalSettingRepository->updateEnvVariables($envUpdates);
            }

            return response()->json([
                'code' => 200,
                'message' => __('Payment settings updated successfully.')
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => 'Error updating payment settings: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateInvoiceSettings(UpdateInvoiceSettingsRequest $request): JsonResponse
    {
        try {
            $data = $request->all();
            if ($request->hasFile('invoice_logo')) {
                $data['invoice_logo'] = $request->file('invoice_logo');
            }
            $this->globalSettingRepository->updateInvoiceSettings($data);

            return response()->json([
                'code' => 200,
                'message' => __('Invoice settings updated successfully.')
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => 'Error updating invoice settings: ' . $e->getMessage()
            ], 500);
        }
    }


    public function dbSettings(Request $request)
    {
        echo "Here";
    }
    public function indexInvoiceSettings(Request $request): JsonResponse
    {
        $groupId = $request->input('group_id');
        $langCode = App::getLocale();
        $language = Language::where('code', $langCode)->first();
        $languageId = $language->id;

        if ($groupId == 14) {

            if (request()->has('language_id') && !empty($request->language_id) && $request->language_id != $language->id) {
                $languageId = $request->language_id;
            }
            $settings = GlobalSetting::where(['group_id' => $groupId, 'language_id' => $languageId])->get();
        } elseif ($groupId == 8) {
            if (request()->has('language_id') && !empty($request->language_id) && $request->language_id != $language->id) {
                $languageId = $request->language_id;
            }
            $settings = GlobalSetting::where(['group_id' => $groupId, 'language_id' => $languageId])->first();
        } elseif ($groupId == 10) {
            if (request()->has('language_id') && !empty($request->language_id) && $request->language_id != $language->id) {
                $languageId = $request->language_id;
            }
            $settings = GlobalSetting::where(['group_id' => $groupId, 'language_id' => $languageId])->get();
        } else {

            $settings = GlobalSetting::where('group_id', $groupId)->get();

            $settings->transform(function ($setting) {
                if ($setting->key === 'invoice_company_logo') {
                    $setting->value = GlobalSetting::file($setting->value);
                }
                return $setting;
            });
        }


        return response()->json([
            'code' => 200,
            'message' => __('Data retrieved successfully'),
            'data' => [
                'settings' => $settings ?? [],
            ],
        ], 200);
    }

    public function updateAdminCommission(AdminCommissionRequest $request): JsonResponse
    {
        try {
            $settings = $request->except(['_token']);
            $commission_type = $request->commission_type;

            $data = [];
            foreach ($settings as $key => $value) {
                if ($key == 'commission_rate') {
                    $key = $commission_type == 'percentage' 
                        ? 'commission_rate_percentage' 
                        : 'commission_rate_fixed';
                }
                
                if ($key != 'group_id') {
                    $data[$key] = $value;
                }
            }

            $this->globalSettingRepository->updateMultiple(
                $data,
                $request->group_id
            );

            return response()->json([
                'code' => 200,
                'message' => __('Global setting updated successfully.')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => 'Error updating admin commission: ' . $e->getMessage()
            ], 500);
        }
    }

    public function saveTaxOptions(TaxOptionsRequest $request): JsonResponse
    {
        try {
            $method = $request->method;
            $taxOptions = $request->except(['_token', 'method', 'tax_type_id', 'tax_rate_id']);
            
            if ($method == 'add') {
                $taxCount = GlobalSetting::where('group_id', 3)->count() / 3 + 1;
                $data = [];
                
                foreach ($taxOptions as $key => $value) {
                    $data[$key . '_' . $taxCount] = $value;
                }
                
                $this->globalSettingRepository->updateMultiple($data, $request->group_id);
                
                return response()->json([
                    'code' => 200,
                    'message' => __('tax_options_create_success')
                ]);
            } else {
                if ($request->has('tax_type')) {
                    GlobalSetting::where('id', $request->tax_type_id)->update([
                        'value' => $request->tax_type,
                        'group_id' => $request->group_id,
                    ]);
                }
                
                if ($request->has('tax_rate')) {
                    GlobalSetting::where('id', $request->tax_rate_id)->update([
                        'value' => $request->tax_rate,
                        'group_id' => $request->group_id,
                    ]);
                }
                
                return response()->json([
                    'code' => 200,
                    'message' => __('tax_options_update_success')
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => 'Error processing tax options: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteTaxOptions(Request $request): JsonResponse
    {
        try {
            $taxOptions = $request->except(['_token']);
            
            foreach ($taxOptions as $taxOptionKey => $taxOptionValue) {
                $this->globalSettingRepository->deleteByKey($taxOptionValue);
            }

            return response()->json([
                'code' => 200,
                'message' => __('tax_options_delete_success')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => 'Error deleting tax options: ' . $e->getMessage()
            ], 500);
        }
    }

    public function taxStatusChange(Request $request): JsonResponse
    {
        try {
            $this->globalSettingRepository->updateTaxStatus(
                $request->tax_type_staus,
                $request->status
            );

            return response()->json([
                'code' => 200,
                'message' => __('Status updated successfully.')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => 'Error changing status tax options: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateOtpSettings(OTPSettingsRequest $request): JsonResponse
    {
        try {
            $settings = $request->except(['_token']);
            $languageId = $this->getLanguageId($request);

            if ($request->has('how_it_work_content')) {
                $data = [];
                foreach ($settings as $key => $value) {
                    if ($key != 'group_id' && $key != 'language_id') {
                        $newKey = $key == 'how_it_work_content' 
                            ? 'how_it_work_content_' . $languageId 
                            : $key;
                        $data[$newKey] = $value;
                    }
                }
                
                $this->globalSettingRepository->updateMultiple(
                    $data,
                    $request->group_id,
                    $languageId
                );
            } else {
                $this->handleTimezoneUpdate($request);
                $data = array_filter($settings, fn($key) => $key != 'group_id' );
                
                $this->globalSettingRepository->updateMultiple(
                    $data,
                    $request->group_id
                );
            }

            return response()->json([
                'code' => 200,
                'message' => __('Global setting updated successfully.')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => 'Error updating global settings: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updatesearchSettings(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'milesradius' => 'required',
            'goe_key' => 'required',
        ], [
            'milesradius.required' => __('Miles Radius is required.'),
            'goe_key.required' => __('Google map key is required.'),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'code' => 422,
                'errors' => $validator->messages()->toArray(),
            ], 422);
        }

        try {
            $this->globalSettingRepository->updateMultiple([
                'milesradious' => $request->milesradius,
                'goglemapkey' => $request->goe_key,
            ], $request->group_id);

            return response()->json([
                'code' => 200,
                'message' => __('Global setting updated successfully.')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => 'Error updating global settings: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updatePreferenceSettings(Request $request): JsonResponse
    {
        try {
            $settings = $request->except(['_token']);
            $data = array_filter($settings, fn($key) => $key != 'group_id', ARRAY_FILTER_USE_KEY);
            
            $this->globalSettingRepository->updateMultiple(
                $data,
                $request->group_id
            );

            return response()->json([
                'code' => 200,
                'message' => __('Global setting updated successfully.')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => 'Error updating global settings: ' . $e->getMessage()
            ], 500);
        }
    }
    protected function getLanguageId(Request $request): int
    {
        $langCode = App::getLocale();
        $language = Language::where('code', $langCode)->first();
        $languageId = $language->id;

        if ($request->has('language_id') && $request->language_id != $language->id) {
            $languageId = $request->language_id;
        }

        return $languageId;
    }

    protected function handleTimezoneUpdate(Request $request): void
    {
        if ($request->has('timezone_format_view') && !empty($request->timezone_format_view)) {
            $path = base_path('.env');
            $timezone = DB::table('timezones')->where('name', $request->timezone_format_view)->first();

            if ($timezone && file_exists($path)) {
                $envContents = file_get_contents($path);

                $newTimezone = 'TIMEZONE_SET=' . $timezone->utc_offset;
                $nameTimezone = 'APP_TIMEZONE=' . $request->timezone_format_view;

                $envContents = preg_replace('/^TIMEZONE_SET=.*$/m', $newTimezone, $envContents);
                $envContents = preg_replace('/^APP_TIMEZONE=.*$/m', $nameTimezone, $envContents);

                file_put_contents($path, $envContents);
            }
        }
    }

    public function updateCopyrightSettings(CopyrightSettingsRequest $request): JsonResponse
    {
        $result = $this->globalSettingRepository->updateCopyrightSettings($request->all());

        return response()->json([
            'code' => $result['success'] ? 200 : 500,
            'message' => $result['message']
        ], $result['success'] ? 200 : 500);
    }

    public function updateCookiesInfoSettings(CookiesSettingsRequest $request): JsonResponse
    {
        $result = $this->globalSettingRepository->updateCookiesSettings($request->all());

        return response()->json([
            'code' => $result['success'] ? 200 : 500,
            'message' => $result['message']
        ], $result['success'] ? 200 : 500);
    }

    public function updateLogoSettings(LogoSettingsRequest $request): JsonResponse
    {
        $result = $this->globalSettingRepository->updateLogoSettings($request->all());

        return response()->json([
            'code' => $result['success'] ? 200 : 500,
            'message' => $result['message']
        ], $result['success'] ? 200 : 500);
    }

    public function indexLogoSettings(Request $request): JsonResponse
    {
        $groupId = $request->input('group_id');

        $settings = GlobalSetting::where('group_id', $groupId)->get();

        $settings->transform(function ($setting) {
            if (in_array($setting->key, ['site_logo', 'site_favicon', 'site_icon', 'site_dark_logo', 'site_mobile_icon'])) {
                $setting->value = GlobalSetting::file($setting->value);
            }
            return $setting;
        });

        return response()->json([
            'code' => 200,
            'message' => __('Data retrieved successfully'),
            'data' => [
                'settings' => $settings,
            ],
        ], 200);
    }

    public function updateCustomSettings(CustomSettingsRequest $request): JsonResponse
    {
        $result = $this->globalSettingRepository->updateCustomSettings($request->all());

        return response()->json([
            'code' => $result['success'] ? 200 : 500,
            'message' => $result['message']
        ], $result['success'] ? 200 : 500);
    }

    public function indexCustomSettings(Request $request): JsonResponse
    {
        $groupId = $request->input('group_id');
        $settings = $this->globalSettingRepository->getCustomSettings($groupId);

        return response()->json([
            'code' => 200,
            'message' => __('Data retrieved successfully'),
            'data' => [
                'settings' => $settings,
            ],
        ], 200);
    }

    public function searchSettings(): View
    {
        $dateFormats = DB::table('date_formats')->select('name')->get();
        $timeFormats = DB::table('time_formats')->select('name')->get();
        $timezones = DB::table('timezones')->select('name')->get();

        return view('globalsetting::setting.search-settings', compact('dateFormats', 'timeFormats', 'timezones'));
    }
    public function localizationSettings(): View
    {
        $dateFormats = DB::table('date_formats')->select('name')->get();
        $timeFormats = DB::table('time_formats')->select('name')->get();
        $timezones = DB::table('timezones')->select('name')->get();

        return view('globalsetting::setting.dt-settings', compact('dateFormats', 'timeFormats', 'timezones'));
    }

    public function updateAppointmentSettings(Request $request): JsonResponse
    {
        $settings = $request->except(['_token']);

        try {

            foreach ($settings as $key => $value) {
                if ($key != 'group_id') {
                    GlobalSetting::updateOrCreate(
                        ['key' => $key],
                        [
                            'value' => $value,
                            'group_id' => $request->group_id
                        ]
                    );
                }
            }

            return response()->json([
                'code' => 200,
                'message' => __('appointment_setting_update_success')
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => __('appointment_setting_update_error'),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function googleMapStatus(Request $request)
    {
        $rules = [
            'google_map_status' => 'required|in:0,1',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $saveSiteKey = GlobalSetting::updateOrCreate(
            ['key' => 'google_map_status'],
            ['value' => $request->google_map_status, 'group_id' => 32]
        );

        $message = $request->google_map_status == 1 ? 'Activated' : 'Blocked';

        return response()->json(['code' => 200, 'message' => $message, 'data' => []], 200);
    }

    public function clearCache(): JsonResponse
    {
        try {
            Artisan::call('optimize:clear');
            return $this->jsonResponse(200, __('cache_clear_success'));
        } catch (\Exception $e) {
            return $this->jsonResponse(500, __('cache_clear_error'));
        }
    }

    protected function jsonResponse(int $code, string $message, $data = null, array $additional = [])
    {
        $response = [
            'code'    => $code,
            'message' => $message,
            'data'    => $data
        ];

        return response()->json(array_merge($response, $additional), $code);
    }
}
