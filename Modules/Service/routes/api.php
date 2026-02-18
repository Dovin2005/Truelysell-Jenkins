<?php

use Illuminate\Support\Facades\Route;
use Modules\Service\app\Http\Controllers\ServiceController;

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

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('service', ServiceController::class)->names('service');
});
Route::prefix('services')->group(function () {
    Route::post('/list', [ServiceController::class, 'index']);
    Route::post('/save', [ServiceController::class, 'store'])->name('addservice');
    Route::post('/saveproduct', [ServiceController::class, 'store'])->name('addproduct');
    Route::post('/delete', [ServiceController::class, 'delete'])->name('deleteservice');
    Route::post('/set-default', [ServiceController::class, 'setdefault'])->name('setdefault');

}); 

Route::prefix('provider')->group(function () {
    Route::post('/service-list', [ServiceController::class, 'providerServiceIndex']);
    Route::post('/service-details/{slug}', [ServiceController::class, 'getDetails']);
    Route::delete('/delete-service-image/{id}', [ServiceController::class, 'deleteServiceImage']);
    Route::delete('/delete-slot/{id}', [ServiceController::class, 'deleteSlot']);
    Route::delete('/delete-additional-services/{id}', [ServiceController::class, 'deleteAdditionalServices']);
    Route::post('/service/delete', [ServiceController::class, 'deleteServices']);
    Route::post('/service/status', [ServiceController::class, 'status']);
    Route::post('/service/check-unique', [ServiceController::class, 'checkUnique']);
    Route::post('/service/edit/check-unique', [ServiceController::class, 'checkEditUnique']);
    Route::post('/service/save', [ServiceController::class, 'providerServiceStore']);
    Route::post('/service/update', [ServiceController::class, 'providerServiceUpdate']);
    Route::post('/subscription/detail', [ServiceController::class, 'providerSubApi']);
}); 
Route::post('/translate/', [ServiceController::class, 'translate']);