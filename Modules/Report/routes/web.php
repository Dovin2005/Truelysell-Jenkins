<?php

use Illuminate\Support\Facades\Route;
use Modules\Report\app\Http\Controllers\ReportController;

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

Route::group(['prefix' => 'admin', 'middleware' => ['admin.auth']], function () {
    Route::get('/reports', function () {
        return view('report::admin.paymentreport');
    })->name('admin.payment-report');
});

Route::group(['prefix' => 'provider', 'middleware' => ['auc', 'permission']], function () {
    Route::get('/reports', [ReportController::class, 'getProviderPaymentList'])->name('provider.payment-report');
});