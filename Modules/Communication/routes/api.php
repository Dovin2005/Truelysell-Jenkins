<?php

use Illuminate\Support\Facades\Route;
use Modules\Communication\app\Http\Controllers\CommunicationController;
use Modules\Communication\app\Http\Controllers\EmailController;
use Modules\Communication\app\Http\Controllers\SmsController;
use Modules\Communication\app\Http\Controllers\NotificationController;
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

Route::prefix('mail')->group(function () {
    Route::post('/sendmail', [EmailController::class, 'sendEmail']);
});
Route::prefix('sms')->group(function () {
    Route::post('/sendsms', [SmsController::class, 'Sendsms']);
    Route::post('/getmsgserviceid',[SmsController::class, 'getmsgserviceid']);
});
Route::prefix('notification')->group(function () {
    Route::post('/savefcmtoken', [NotificationController::class, 'saveToken']);
    Route::post('/Storenotification',[NotificationController::class, 'Storenotification']);
    Route::post('/notificationlist',[NotificationController::class, 'notificationlist'])->name('notificationdata');
    Route::post('/updatereadstatus',[NotificationController::class, 'updatereadstatus']);
    Route::get('/getnotificationcount',[NotificationController::class, 'getnotificationcount']);
});

Route::post('/otp-settingsapi', [CommunicationController::class, 'getOtpSettingsApi']);
Route::post('/verify-otp-api', [CommunicationController::class, 'verifyOtpApi']);
