<?php

use Illuminate\Support\Facades\Route;
use Modules\Advertisement\app\Http\Controllers\AdvertisementController;

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
    Route::resource('advertisement', AdvertisementController::class)->names('advertisement');
});

Route::get('/admin/advertisements',[AdvertisementController::class, 'advertisement'])->name('admin.advertisement')->middleware('admin.auth', 'permission');
Route::post('/admin/advertisement/create',[AdvertisementController::class, 'create'])->name('admin.advertisement.create')->middleware('admin.auth');
Route::post('/admin/advertisement/index',[AdvertisementController::class, 'index'])->name('admin.advertisement.index')->middleware('admin.auth');
Route::post('/admin/advertisement/edit',[AdvertisementController::class, 'edit'])->name('admin.advertisement.edit')->middleware('admin.auth');
Route::post('/admin/advertisement/delete',[AdvertisementController::class, 'delete'])->name('admin.advertisement.delete')->middleware('admin.auth');
