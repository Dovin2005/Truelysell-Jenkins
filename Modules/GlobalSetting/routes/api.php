<?php

use Illuminate\Support\Facades\Route;
use Modules\Faq\app\Http\Controllers\FaqController;
use Modules\GlobalSetting\app\Http\Controllers\CredentialSettingController;
use Modules\GlobalSetting\app\Http\Controllers\GlobalSettingController;
use Modules\GlobalSetting\app\Http\Controllers\LanguageController;
use Modules\GlobalSetting\app\Http\Controllers\DbbackupController;
use Modules\GlobalSetting\app\Http\Controllers\CurrencyController;
use Modules\GlobalSetting\app\Http\Controllers\CommunicationSettingsController;
use Modules\GlobalSetting\app\Http\Controllers\InvoiceTemplateController;
use Modules\GlobalSetting\app\Http\Controllers\SubscriptionPackageController;

/*
 *--------------------------------------------------------------------------
 * API Routes
 *--------------------------------------------------------------------------
 *
 * Here is where you can register API routes for your application. These
 * routes are loaded by the RouteServiceProvider within a group which
 * is assigned the "api" middleware group. Enjoy building your API!
 *
*/

Route::group(['prefix' => 'admin/general-setting'], function() {
    Route::post('/list', [GlobalSettingController::class, 'index']);
    Route::get('/{id}', [GlobalSettingController::class, 'show']);
    Route::post('/', [GlobalSettingController::class, 'store']);
    Route::put('/{id}', [GlobalSettingController::class, 'update']);
    Route::delete('/{id}', [GlobalSettingController::class, 'destroy']);
});

Route::post('/admin/update-general-setting', [GlobalSettingController::class, 'updateGeneralSettings']);
Route::post('/admin/updatepaymentSettings', [GlobalSettingController::class, 'updatepaymentSettings']);

Route::post('/admin/update-invoice-setting', [GlobalSettingController::class, 'updateInvoiceSettings']);
Route::post('/admin/index-invoice-setting', [GlobalSettingController::class, 'indexInvoiceSettings']);
Route::post('/admin/update-otp-setting', [GlobalSettingController::class, 'updateOtpSettings']);
Route::post('/admin/update-search-setting', [GlobalSettingController::class, 'updatesearchSettings']);
Route::post('/admin/update-google-map-status', [GlobalSettingController::class, 'googleMapStatus']);
Route::post('/admin/update-appointment-setting', [GlobalSettingController::class, 'updateAppointmentSettings']);

Route::post('/admin/update-preference-setting', [GlobalSettingController::class, 'updatePreferenceSettings']);
Route::post('/admin/update-copyright-setting', [GlobalSettingController::class, 'updateCopyrightSettings']);
Route::post('/admin/update-cookies-info-setting', [GlobalSettingController::class, 'updateCookiesInfoSettings']);
Route::post('/admin/update-logo-setting', [GlobalSettingController::class, 'updateLogoSettings']);
Route::post('/admin/index-logo-setting', [GlobalSettingController::class, 'indexLogoSettings']);
Route::post('/admin/update-bread-image-setting', [GlobalSettingController::class, 'updateBreadImageSettings']);
Route::post('/admin/index-bread-image-setting', [GlobalSettingController::class, 'indexBreadImageSettings']);
Route::post('/admin/index-custom-setting', [GlobalSettingController::class, 'indexCustomSettings']);
Route::post('/admin/update-custom-setting', [GlobalSettingController::class, 'updateCustomSettings']);
Route::post('/admin/dbbacklist', [DbbackupController::class, 'index']);

Route::post('/admin/languages', [LanguageController::class, 'index']);
Route::post('/admin/languages/store', [LanguageController::class, 'store']);
Route::post('/admin/languages/deleteLanguage', [LanguageController::class, 'deleteLanguage']);
Route::post('/admin/add-invoice-template', [InvoiceTemplateController::class, 'store']);
Route::post('/admin/index-invoice-template', [InvoiceTemplateController::class, 'index']);
Route::post('/admin/destroy-invoice-template', [InvoiceTemplateController::class, 'destroy']);
Route::post('/admin/invoice-template/set-default', [InvoiceTemplateController::class, 'setDefault']);
Route::post('/translate', [LanguageController::class, 'translate']);

//email settings
Route::post('/settings/emailsettings/store', [CommunicationSettingsController::class, 'store']);
Route::post('/settings/emailsettings/setdefault',[CommunicationSettingsController::class, 'setdefault']);
Route::post('/settings/getsettingsdata',[CommunicationSettingsController::class, 'getsettingsdata']);
Route::post('/settings/getemailsettings',[CommunicationSettingsController::class, 'list']);
Route::post('/admin/languages/checkUnique', [LanguageController::class, 'checkUnique']);
Route::post('/settings/communication/storesettings', [CommunicationSettingsController::class, 'store']);
Route::post('/settings/sms/storesmsstatus', [CommunicationSettingsController::class, 'statusstore']);
Route::post('/settings/gettemplatelist', [CommunicationSettingsController::class, 'gettemplatelist']);
Route::post('/settings/templates/store',[CommunicationSettingsController::class, 'templatestore']);
Route::post('settings/templates/deletetemplate',[CommunicationSettingsController::class, 'deletetemplate']);
Route::post('/settings/edittemplate', [CommunicationSettingsController::class, 'edit'])->name('settings.edittemplate');
Route::post('/settings/communication/save-configuaration', [CommunicationSettingsController::class, 'saveConfiguration']);

Route::post('/admin/update-admin-commission', [GlobalSettingController::class, 'updateAdminCommission']);

Route::post('/admin/save-tax-options', [GlobalSettingController::class, 'saveTaxOptions']);
Route::post('admin/delete-tax-options', [GlobalSettingController::class, 'deleteTaxOptions']);
Route::post('admin/tax-status-change', [GlobalSettingController::class, 'taxStatusChange']);

Route::prefix('admin')->group(function () {
    Route::post('languages/change-status', [LanguageController::class, 'changeStatus']);
});

Route::prefix('currencies')->group(function () {
    Route::post('/list', [CurrencyController::class, 'index']);
    Route::post('/save', [CurrencyController::class, 'store']);
    Route::post('/delete', [CurrencyController::class, 'destroy']);
    Route::post('/set-default', [CurrencyController::class, 'setDefault']);
    Route::post('/change-status', [CurrencyController::class, 'changeStatus']);
    Route::post('/checkUnique', [CurrencyController::class, 'checkUnique']);
});

Route::prefix('credential')->group(function () {
    Route::post('/list', [CredentialSettingController::class, 'index']);
    Route::post('/save/recaptcha', [CredentialSettingController::class, 'storeRecaptahca']);
    Route::post('/save/tag-manager', [CredentialSettingController::class, 'storeTagManager']);
    Route::post('/save/analytics', [CredentialSettingController::class, 'storeAnalytics']);
    Route::post('/save/sso', [CredentialSettingController::class, 'storeSSO']);
    Route::post('/status/recaptcha', [CredentialSettingController::class, 'statusRecaptahca']);
    Route::post('/status/tag-manager', [CredentialSettingController::class, 'statusTagManager']);
    Route::post('/status/analytics-status', [CredentialSettingController::class, 'statusAnalytics']);
    Route::post('/status/sso-configure-status', [CredentialSettingController::class, 'statusSSO']);
    Route::post('/save/chatgpt', [CredentialSettingController::class, 'storechatgpt']);
    Route::post('/status/chatgpt-status', [CredentialSettingController::class, 'updatechatgptstatus']);
    Route::post('/save/location', [CredentialSettingController::class, 'storeLocation']);
    Route::post('/status/location-status', [CredentialSettingController::class, 'updatelocationstatus']);
    Route::post('/status/recaptcha-status', [CredentialSettingController::class, 'updaterecaptchastatus']);
    Route::post('/save/googlerecaptcha', [CredentialSettingController::class, 'googlerecaptcha']);

});

Route::prefix('subscription-package')->group(function () {
    Route::post('/list', [SubscriptionPackageController::class, 'listAll']);
    Route::post('/subscription-detail', [SubscriptionPackageController::class, 'index']);
    Route::post('/save', [SubscriptionPackageController::class, 'store']);
    Route::post('/update', [SubscriptionPackageController::class, 'update']);
    Route::post('/delete', [SubscriptionPackageController::class, 'delete']);
});

Route::prefix('faq')->group(function () {
    Route::post('/faq-detail', [FaqController::class, 'index']);
    Route::post('/save', [FaqController::class, 'store']);
    Route::post('/update', [FaqController::class, 'update']);
    Route::post('/delete', [FaqController::class, 'delete']);
});


