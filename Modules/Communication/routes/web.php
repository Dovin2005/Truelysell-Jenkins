<?php

use Illuminate\Support\Facades\Route;
use Modules\Communication\app\Http\Controllers\CommunicationController;
use Modules\Communication\app\Http\Controllers\NotificationController;

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

Route::group([], function () {
    Route::post('/otp-settings', [CommunicationController::class, 'getOtpSettings']);
    Route::post('/verify-otp', [CommunicationController::class, 'verifyOtp']);
    Route::post('/register-otp-settings', [CommunicationController::class, 'getRegisterOtpSettings']);
    Route::post('/provider-register-otp-settings', [CommunicationController::class, 'getProviderRegisterOtpSettings']);
    Route::post('/register-verify-otp', [CommunicationController::class, 'verifyRegisterOtp']);
});
Route::prefix('notification')->group(function () {
    Route::post('/savefcmtoken', [NotificationController::class, 'saveToken']);
});
Route::get('user/notifications',[NotificationController::class, 'notificationlist'])->name('user.notification')->middleware('auc');
Route::get('provider/notifications',[NotificationController::class, 'notificationlist'])->name('provider.notification')->middleware('auc');
Route::get('admin/notifications',[NotificationController::class, 'notificationlist'])->name('admin.notification')->middleware('admin.auth', 'permission');
