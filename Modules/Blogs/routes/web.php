<?php

use Illuminate\Support\Facades\Route;
use Modules\Blogs\app\Http\Controllers\BlogsController;

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
    Route::get('/blog-category', function () {
        return view('blogs::blogs.blog-category');
    })->name('admin.blog-category')->middleware('admin.auth', 'permission');

    Route::get('/blog-post', [BlogsController::class, 'blogPost'])->name('admin.blog-post')->middleware('admin.auth', 'permission');
});

Route::get('/blogs', [BlogsController::class, 'blogList'])->name('blog-list');
Route::get('/blog-details/{slug}', [BlogsController::class, 'blogDetails'])->name('blog-details');
