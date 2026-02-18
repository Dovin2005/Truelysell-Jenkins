<?php

use Illuminate\Support\Facades\Route;
use Modules\Categories\app\Http\Controllers\CategoriesController;

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
    Route::resource('categories', CategoriesController::class)->names('categories');
});

Route::post('/get-subcategories', [CategoriesController::class, 'getSubcategories'])->name('get.subcategories');

Route::get('admin/test', [CategoriesController::class, 'index']);