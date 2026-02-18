<?php

use Illuminate\Support\Facades\Route;
use Modules\Coupon\app\Http\Controllers\CouponController;

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

Route::group(['prefix' => 'admin', 'middleware' => ['admin.auth', 'permission']], function () {

    Route::get('/coupons',  [CouponController::class, 'index'])->name('admin.coupon');
    Route::get('/create-coupon',  [CouponController::class, 'create'])->name('admin.create-coupon');
    Route::get('/edit-coupon/{id}',  [CouponController::class, 'edit'])->name('admin.edit-coupon');
        
});

Route::group(['prefix' => 'provider', 'middleware' => ['auc', 'permission']], function () {

    Route::get('/coupons',  [CouponController::class, 'index'])->name('provider.coupon');
    Route::get('/create-coupon',  [CouponController::class, 'create'])->name('provider.create-coupon');
    Route::get('/edit-coupon/{id}',  [CouponController::class, 'edit'])->name('provider.edit-coupon');
        
});

Route::group(['prefix' => 'coupon'], function() {
    Route::post('/check-unique', [CouponController::class, 'checkUnique']);
    Route::post('/save',  [CouponController::class, 'store']);
    Route::post('/list', [CouponController::class, 'couponList']);
    Route::post('/change-status', [CouponController::class, 'changeCouponStatus']);
    Route::post('/delete', [CouponController::class, 'destroy']);
});
