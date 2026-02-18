<?php

use Illuminate\Support\Facades\Route;
use Modules\RolesPermissions\app\Http\Controllers\RolesPermissionsController;

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
    Route::group(['prefix' => 'role', 'middleware' => 'api'], function() {
        Route::post('/list', [RolesPermissionsController::class, 'index']);
        Route::post('/save', [RolesPermissionsController::class, 'store']);
        Route::post('/delete', [RolesPermissionsController::class, 'destroy']);
        Route::post('/change-status', [RolesPermissionsController::class, 'roleStatusChange']);
        Route::post('/check-unique', [RolesPermissionsController::class, 'checkUniqueRoleName']);
    });

    Route::group(['prefix' => 'permission', 'middleware' => 'api'], function() {
        Route::post('/list', [RolesPermissionsController::class, 'permissionList']);
        Route::post('/update', [RolesPermissionsController::class, 'permissionUpdate']);
    });
});
