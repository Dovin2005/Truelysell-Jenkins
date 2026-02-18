<?php

use Illuminate\Support\Facades\Route;
use Modules\Testimonials\app\Http\Controllers\TestimonialsController;

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
    Route::get('/testimonials', function () {
        return view('testimonials::testimonials.testimonials');
    })->name('admin.testimonials')->middleware('admin.auth', 'permission');
});
