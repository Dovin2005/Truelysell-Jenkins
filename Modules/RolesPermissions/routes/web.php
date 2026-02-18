<?php

use Illuminate\Support\Facades\Route;
use Modules\RolesPermissions\app\Http\Controllers\RolesPermissionsController;

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

Route::group(['prefix' => 'admin'], function () {
    Route::get('/roles-permissions', function () {
        return view('rolespermissions::admin.roles-permissions');
    })->name('admin.roles-permissions')->middleware('admin.auth', 'permission');

    Route::get('/save-modules/{type}', [RolesPermissionsController::class, 'saveModules']);

});



Route::middleware(['auc'])->group(function () {

    Route::get('provider/roles-permissions', function () {
        return view('rolespermissions::provider.roles_permissions');
    })->name('provider.roles-permissions')->middleware('permission');

    Route::group(['prefix' => 'role'], function() {
        Route::post('/list', [RolesPermissionsController::class, 'index']);
        Route::post('/save', [RolesPermissionsController::class, 'store']);
        Route::post('/delete', [RolesPermissionsController::class, 'destroy']);
        Route::post('/change-status', [RolesPermissionsController::class, 'roleStatusChange']);
        Route::post('/check-unique', [RolesPermissionsController::class, 'checkUniqueRoleName']);
    });

    Route::group(['prefix' => 'permission'], function() {
        Route::post('/list', [RolesPermissionsController::class, 'permissionList']);
        Route::post('/update', [RolesPermissionsController::class, 'permissionUpdate']);
    });
});
