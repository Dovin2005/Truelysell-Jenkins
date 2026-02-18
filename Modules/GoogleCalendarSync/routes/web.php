<?php

use Illuminate\Support\Facades\Route;
use Modules\GoogleCalendarSync\app\Http\Controllers\GoogleCalendarSyncController;

Route::group(['prefix' => 'admin', 'middleware' => ['admin.auth', 'permission']], function () {
    Route::get('/googlecalendarsyncs', [GoogleCalendarSyncController::class, 'index'])->name('admin.googlecalendarsync');
});

Route::get('/provider/googlecalendarsyncs', [GoogleCalendarSyncController::class, 'showCalendar'])
    ->name('provider.synccalendar')
    ->middleware(['auth']);

Route::post('/provider/sync-calendar-status', [GoogleCalendarSyncController::class, 'updateCalendarStatus'])
    ->name('provider.sync.calendar')
    ->middleware(['auth']);

// Route to save the Google API credentials (for admins)
Route::post('/provider/google-credentials', [GoogleCalendarSyncController::class, 'saveGoogleCredentials'])
    ->name('provider.google.credentials.save')
    ->middleware(['auth']); // Add admin middleware if you have one

// Route to redirect the provider to Google for authorization
Route::get('/provider/google-connect', [GoogleCalendarSyncController::class, 'redirectToGoogle'])
    ->name('provider.google.connect')
    ->middleware(['auth']);

// Route Google redirects back to after authorization (Callback URL)
Route::get('/oauth/google/callback', [GoogleCalendarSyncController::class, 'handleGoogleCallback'])
    ->name('provider.google.callback')
    ->middleware(['auth']);

// Route to disconnect the provider's Google Calendar
Route::post('/provider/google-disconnect', [GoogleCalendarSyncController::class, 'disconnectGoogleCalendar'])
    ->name('provider.google.disconnect')
    ->middleware(['auth']);
    