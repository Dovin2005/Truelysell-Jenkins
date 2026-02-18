<?php

use Illuminate\Support\Facades\Route;
use Modules\Service\app\Http\Controllers\ServiceController;

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
    Route::resource('service', ServiceController::class)->names('service');
});
Route::prefix('services')->group(function () {
    Route::post('/list', [ServiceController::class, 'index']);
    Route::post('/save', [ServiceController::class, 'store'])->name('addservice');
    Route::post('/update', [ServiceController::class, 'update'])->name('updateservice');
});
Route::prefix('provider')->group(function () {
    Route::get('/service/create', [ServiceController::class, 'providerAddServiceIndex'])->name('provider.add.service')->middleware('auc', 'permission');
    Route::post('/service/save', [ServiceController::class, 'providerServiceStore'])->name('provider.service.store');
    Route::post('/service/update', [ServiceController::class, 'providerServiceUpdate'])->name('provider.service.update');
    Route::post('/subscription/detail', [ServiceController::class, 'providerSub']);
});
Route::get('/provider/service', [ServiceController::class, 'providerService'])->name('provider.service')->middleware('auc', 'permission');
Route::get('/provider/edit', [ServiceController::class, 'providerEditService'])->name('provider.edit.service')->middleware('auc', 'permission');
Route::post('/image-delete', [ServiceController::class, 'deleteImage'])->name('image.delete');

Route::post('/check-coupon', [ServiceController::class, 'checkCoupon'])->name('check-coupon');
Route::post('admin/verify-service', [ServiceController::class, 'verifyService'])->name('admin.verify.service');
