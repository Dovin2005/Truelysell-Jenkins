<?php

use Illuminate\Support\Facades\Route;
use Modules\Coupon\app\Http\Controllers\CouponController;

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
    Route::group(['prefix' => 'coupon', 'middleware' => 'api'], function() {
        Route::post('/check-unique', [CouponController::class, 'checkUnique']);
        Route::post('/save',  [CouponController::class, 'store']);
        Route::post('/list', [CouponController::class, 'couponList']);
        Route::post('/change-status', [CouponController::class, 'changeCouponStatus']);
        Route::post('/delete', [CouponController::class, 'destroy']);
    });
});
