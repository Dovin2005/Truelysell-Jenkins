<?php

use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;
use Modules\Leads\app\Http\Controllers\LeadsController;
use Modules\Leads\app\Http\Controllers\ProviderController;
use Modules\Leads\app\Http\Controllers\UserController;

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

Route::middleware(['auth:sanctum'])->prefix('')->group(function () {
    Route::apiResource('leads', LeadsController::class)->names('leads');


});
Route::post('/leads/admin/list', [LeadsController::class, 'formInputAdminList']);
Route::post('/leads/list', [LeadsController::class, 'userList']);
Route::post('/update/leads', [LeadsController::class, 'updateProviderLeads']);
Route::post('/list/leads', [LeadsController::class, 'listProviderLeads']);
Route::post('/leads/user/store', [LeadsController::class, 'formInputUserStore']);
Route::post('/leads/user/list', [LeadsController::class, 'formInputUserList']);
Route::post('/leads/admin/status', [LeadsController::class, 'formInputStatus']);
Route::post('/leads/provider/status', [LeadsController::class, 'providerFormsInpuStatus']);
Route::post('/leads/provider/list', [LeadsController::class, 'listProviders']);


Route::post('/provider/update-quote', [ProviderController::class, 'updateQuote']);

Route::post('/leads/user/status', [UserController::class, 'userStatus']);
Route::post('/storepayments', [LeadsController::class, 'storepayments']);

Route::post('/leads/transaction-list', [TransactionController::class, 'leadsTransactionList'])->middleware('auth:sanctum');
