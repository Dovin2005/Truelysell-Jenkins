<?php

use Illuminate\Support\Facades\Route;
use Modules\MenuBuilder\app\Http\Controllers\MenuBuilderController;

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

Route::group(['prefix' => 'admin/content'], function () {
    Route::get('/menu-builder', function() {
        return view('menubuilder::menus.menu-builder');
    })->name('content.menu-builder')->middleware('admin.auth', 'permission');
});
