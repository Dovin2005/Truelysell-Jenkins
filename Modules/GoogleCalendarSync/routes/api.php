<?php

use Illuminate\Support\Facades\Route;
use Modules\GoogleCalendarSync\Http\Controllers\GoogleCalendarSyncController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('googlecalendarsyncs', GoogleCalendarSyncController::class)->names('googlecalendarsync');
});
