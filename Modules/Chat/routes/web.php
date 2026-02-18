<?php

use Illuminate\Support\Facades\Route;
use Modules\Chat\app\Http\Controllers\ChatController;

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
Route::get('/provider/chat', [ChatController::class, 'providerChat'])->name('providers.chat')->middleware('auc');
Route::get('provider/chat/{user_id}', [ChatController::class, 'providerChat'])->name('provider.chat.with-user')->middleware('auc');
Route::post('provider/send-message', [ChatController::class, 'sendChat'])->name('provider.send.message')->middleware('auc');
Route::post('provider/fetch-messages', [ChatController::class, 'fetchMessages'])->name('provider.fetch.message')->middleware('auc');
Route::get('/provider/get-unread-messages', [ChatController::class, 'getProviderUnreadMessages']);
Route::post('/provider/mark-messages-read', [ChatController::class, 'markProviderMessagesRead']);


Route::get('/user/chat', [ChatController::class, 'userChat'])->name('users.chat')->middleware('auc');
Route::post('/user/chat/mark-read', [ChatController::class, 'markRead'])->name('chat.markRead');
Route::get('/user/get-unread-messages', [ChatController::class, 'getUnreadMessages']);
Route::post('/user/mark-messages-read', [ChatController::class, 'markMessagesRead']);


Route::get('/user/chat/{user_id}', [ChatController::class, 'userChat'])->name('users.chat.with-user')->middleware('auc');
Route::post('user/send-message', [ChatController::class, 'sendChat'])->name('provider.send.message')->middleware('auc');
Route::post('user/fetch-messages', [ChatController::class, 'fetchMessages'])->name('provider.fetch.message')->middleware('auc');

Route::get('/admin/chat', [ChatController::class, 'adminChat'])->name('admin.chat')->middleware('admin.auth', 'permission');
Route::post('admin/send-message', [ChatController::class, 'sendChat'])->name('admin.send.message')->middleware('admin.auth');
Route::post('admin/fetch-messages', [ChatController::class, 'fetchMessages'])->name('admin.fetch.message')->middleware('admin.auth');

