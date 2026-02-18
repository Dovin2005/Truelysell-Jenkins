<?php

use Illuminate\Support\Facades\Route;
use Modules\Leads\Http\Controllers\LeadsController;

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


Route::get('/admin/leads', function () {
    return view('leads::leads.leads');
})->name('admin.leads')->middleware('admin.auth', 'permission');

Route::get('/admin/leadsinfo', function () {
    return view('leads::leads.leadsinfo');
})->name('admin.leadsinfo')->middleware('admin.auth', 'permission');


