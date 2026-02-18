<?php

use Illuminate\Support\Facades\Route;
use Modules\GlobalSetting\app\Http\Controllers\GlobalSettingController;
use Modules\GlobalSetting\app\Http\Controllers\CommunicationSettingsController;
use App\Http\Controllers\AdminDashboardController;
use Modules\GlobalSetting\app\Http\Controllers\CurrencyController;
use Modules\GlobalSetting\app\Http\Controllers\LanguageController;
use Modules\GlobalSetting\app\Http\Controllers\SitemapController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::middleware('admin.auth', 'permission')->group(function () {

    Route::group(['prefix' => 'admin/settings'], function() {
        Route::get('/email-settings', function () {
            return view('globalsetting::communication.email-settings');
        })->name('settings.email-settings');
        Route::get('/email-templates', [CommunicationSettingsController::class, 'add'])->name('settings.email-templates');
        Route::get('/sms-settings', function () {
            return view('globalsetting::communication.sms-settings');
        })->name('settings.sms-settings');
        Route::get('/notification-settings', function () {
            return view('globalsetting::communication.notification-settings');
        })->name('settings.notification-settings');

    });

    Route::group(['prefix' => 'admin/setting'], function() {

        Route::get('/general-settings', function () {
            return view('globalsetting::setting.general-settings');
        })->name('admin.general-settings');

        Route::get('/logo-settings', function () {
            return view('globalsetting::setting.logo-settings');
        })->name('admin.logo-settings');

        Route::get('color', [GlobalSettingController::class, 'colorSettings'])->name('admin.colorSettings')->middleware('permission');
        Route::post('colors/update', [GlobalSettingController::class, 'updateColorSettings'])->name('admin.settings.colors.update');

        Route::get('/breadcrumb-settings', function () {
            return view('globalsetting::setting.bread-image-settings');
        })->name('admin.bread-image-settings');

        Route::get('/copyright-settings', function () {
            return view('globalsetting::setting.copyright-settings');
        })->name('admin.copyright-settings');

        Route::get('/otp-settings', function () {
            return view('globalsetting::setting.otp-settings');
        })->name('admin.otp-settings');

        Route::get('/localization-settings', [GlobalSettingController::class, 'localizationSettings'])->name('admin.dt-settings');
        Route::get('/search-settings', [GlobalSettingController::class, 'searchSettings'])->name('admin.search-settings');

        Route::get('/cookies-settings', function () {
            return view('globalsetting::setting.cookies-settings');
        })->name('admin.cookies-settings');

        Route::get('/maintenance-settings', function () {
            return view('globalsetting::setting.maintenance-settings');
        })->name('admin.maintenance-settings');

        Route::get('/currency-settings', [CurrencyController::class, 'currencySettings'])->name('admin.currency-settings');

        Route::get('/commission', function () {
            return view('globalsetting::setting.commission');
        })->name('admin.commission');

        Route::get('/tax-options', function () {
            return view('globalsetting::setting.tax-options');
        })->name('admin.tax-options');

        Route::get('/custom-settings', function () {
            return view('globalsetting::setting.custom-settings');
        })->name('settings.custom-settings');

        Route::get('/apperance-settings', function () {
            return view('globalsetting::setting.apperance-settings');
        })->name('settings.apperance-settings');

        Route::get('/preference', function () {
            return view('globalsetting::setting.preference');
        })->name('admin.preference');

        Route::get('/invoice-settings', function () {
            return view('globalsetting::setting.invoice-settings');
        })->name('admin.invoice-settings');

        Route::get('//appointment-settings', function () {
            return view('globalsetting::setting.appointment_settings');
        })->name('admin.appointment-settings');

        Route::get('/invoice-template', [AdminDashboardController::class, 'add'])->name('admin.invoice-template');

        //Sitemap Settings
        Route::get('sitemap-settings', [SitemapController::class, 'index'])->name('admin.sitemap-settings');
        Route::post('save-sitemap-url', [SitemapController::class, 'store']);
        Route::post('get-sitemap-urls', [SitemapController::class, 'getSitemapUrls']);
        Route::post('delete-sitemapurl', [SitemapController::class, 'deleteSitemapUrl']);

        Route::get('/clear-cache', function () {
            return view('globalsetting::setting.clear-cache-settings');
        })->name('admin.clear-cache-settings');

        Route::post('/clear-cache-data', [GlobalSettingController::class, 'clearCache']);
    });

    Route::post('/admin/languages/set-default', [LanguageController::class, 'setDefault']);

});
