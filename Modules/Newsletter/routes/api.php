<?php

use Illuminate\Support\Facades\Route;
use Modules\Newsletter\app\Http\Controllers\NewsletterController;

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

Route::middleware(['auth:sanctum'])->group(function () {
    Route::group(['prefix' => 'admin/newsletter', 'middleware' => 'api'], function() {
        Route::post('/list-subscriber', [NewsletterController::class, 'index']);
        Route::post('/delete-subscriber', [NewsletterController::class, 'destroy']);
        Route::post('/change-subscriber-status', [NewsletterController::class, 'subscriberStatusChange']);
    });
});
Route::post('admin/newsletter/save-subscriber', [NewsletterController::class, 'store']);
Route::post('admin/get-newsletter-template', [NewsletterController::class, 'getNewsletterTemplate']);
