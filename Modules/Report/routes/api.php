<?php

use Illuminate\Support\Facades\Route;
use Modules\Report\app\Http\Controllers\ReportController;

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

Route::group(['middleware' => 'api'], function() {
    Route::post('/paymentreportlist', [ReportController::class, 'listAllTransactions']);
    Route::post('/providerpaymentreport', [ReportController::class, 'listProviderTransactions']);
});