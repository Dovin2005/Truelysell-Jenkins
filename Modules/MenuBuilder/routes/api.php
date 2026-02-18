<?php

use Illuminate\Support\Facades\Route;
use Modules\MenuBuilder\app\Http\Controllers\MenuBuilderController;

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
    Route::group(['prefix' => 'content/menu-builder', 'middleware' => 'api'], function() {
        Route::post('/get-built-in-menus', [MenuBuilderController::class, 'getBuiltMenus']);
        Route::post('/save-menus', [MenuBuilderController::class, 'store']);
    });
});

Route::group(['prefix' => 'content/menu-builder', 'middleware' => 'api'], function() {
    Route::post('/list-website-menus', [MenuBuilderController::class, 'index']);
});
