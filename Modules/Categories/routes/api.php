<?php

use Illuminate\Support\Facades\Route;
use Modules\Categories\app\Http\Controllers\CategoriesController;
use Modules\Categories\app\Http\Controllers\FormInputController;

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

Route::prefix('categories')->group(function () {
    Route::post('/list', [CategoriesController::class, 'index']);
    Route::post('/save', [CategoriesController::class, 'store']);
    Route::post('/delete', [CategoriesController::class, 'destroy']);
    Route::post('/change-featured', [CategoriesController::class, 'changeFeatured']);
    Route::post('/form-inputs', [FormInputController::class, 'formInputStore']);
    Route::post('/form-inputs/list', [FormInputController::class, 'formInputList']);
    Route::post('/form-inputs/delete', [FormInputController::class, 'formInputDelete']);
    Route::post('/form-inputs/order', [FormInputController::class, 'formInputupdateOrder']);
});

Route::prefix('subcategories')->group(function () {
    Route::post('/list', [CategoriesController::class, 'subcategoryList']);
    Route::post('/save', [CategoriesController::class, 'subcategoryStore']);
});

Route::post('/get-categories', [CategoriesController::class, 'categories']);
Route::post('/get-subcategories', [CategoriesController::class, 'getSubcategories']);
Route::post('/get-register-subcategories', [CategoriesController::class, 'getRegisterSubcategories']);
Route::get('/languages', [CategoriesController::class, 'getAllLanguages']);
Route::get('/categories/{id}', [CategoriesController::class, 'show']);
