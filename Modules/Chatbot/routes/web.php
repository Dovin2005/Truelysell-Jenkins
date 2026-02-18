<?php

use Illuminate\Support\Facades\Route;
use Modules\Chatbot\app\Http\Controllers\ChatbotController;
use Modules\Chatbot\app\Http\Controllers\QuestionController;
use Modules\Chatbot\app\Http\Controllers\ConversationController;
use Illuminate\Support\Facades\Auth;

Route::prefix('api/chatbot')->group(function () {
    Route::get('start', [ChatbotController::class, 'start']);
    Route::post('answer', [ChatbotController::class, 'answer']);
    Route::get('history', [ChatbotController::class, 'history']);
});

Route::prefix('admin/chatbot')->middleware(['auth'])->group(function() {
    Route::get('/', [QuestionController::class, 'index'])->name('chatbot.index');
    Route::get('/create', [QuestionController::class, 'create'])->name('chatbot.create');
    Route::post('/store', [QuestionController::class, 'store'])->name('chatbot.store');
    Route::get('/edit/{id}', [QuestionController::class, 'edit'])->name('chatbot.edit');
    Route::put('/update/{id}', [QuestionController::class, 'update'])->name('chatbot.update');
    Route::delete('/delete/{id}', [QuestionController::class, 'destroy'])->name('chatbot.destroy');

    Route::get('/conversations', [ConversationController::class, 'index'])->name('conversations.index');
    Route::get('/conversations/show/{id}', [ConversationController::class, 'show'])->name('conversations.show');
    Route::delete('/conversations/destroy/{id}', [ConversationController::class, 'destroy'])->name('conversations.destroy');

});

Route::get('/api/check-auth', function() {
    return response()->json([
        'authenticated' => Auth::check(),
        'user' => Auth::check() ? collect(Auth::user())->only(['id', 'name', 'email']) : null
    ]);
});