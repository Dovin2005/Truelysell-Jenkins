<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Page\app\Http\Controllers\Api\PageController as ApiPageController;
use Modules\Page\app\Http\Controllers\PageController;
use Modules\Page\app\Models\Page;

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

Route::middleware('admin.auth', 'permission')->group(function () {

    Route::group(['prefix' => 'admin/content'], function () {
        Route::get('/page-builder', function () {
            return view('page::page.page-builder');
        })->name('admin.page-builder');

        Route::get('/add/page-builder', function () {
            return view('page::page.add_page_builder');
        })->name('admin.add_page_builder');

        Route::get('/edit/page-builder/{encrypted_id}', function (Request $request) {
            $id = customDecrypt($request->encrypted_id ?? '', Page::$pageSecretKey);
            Page::findOrFail($id);
            return view('page::page.edit_page_builder', compact('id'));
        })->name('admin.edit_page_builder');

        Route::get('/page-section', function () {
            return view('page::section.page-section');
        })->name('admin.page-section');
    });

    Route::get('admin/content/footer-builder', function () {
        return view('page::footer.footer_builder');
    })->name('admin.footer-builder');
});

Route::post('/delete-page', [ApiPageController::class, 'deletePage']);
