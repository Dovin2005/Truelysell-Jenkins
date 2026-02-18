<?php

use Illuminate\Support\Facades\Route;
use Modules\Page\app\Http\Controllers\Api\PageController as ApiPageController;
use Modules\Page\app\Http\Controllers\Api\SectionController;
use Modules\Page\app\Http\Controllers\FooterController;

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



Route::prefix('page-builder')->group(function () {
    Route::post('/section-list', [SectionController::class, 'index']);
    Route::post('/page-builder-list', [SectionController::class, 'indexBuilderList']);
    Route::post('/get-page-details', [SectionController::class, 'getDetails']);
    Route::post('/section-store', [SectionController::class, 'store']);
    Route::post('/page-builder-store', [SectionController::class, 'pageBuilderStore']);
    Route::post('/page-builder-update', [SectionController::class, 'pageBuilderUpdate']);
    Route::post('/delete', [SectionController::class, 'delete']);
});

Route::prefix('page-builder')->group(function () {
    Route::post('/page-info', [ApiPageController::class, 'pageBuilderApi']);
});

Route::post('admin/save-footer-builder', [FooterController::class, 'store']);
Route::post('admin/list-footer-builder', [FooterController::class, 'index']);
Route::post('get-footer', [FooterController::class, 'getFooterDetails']);
