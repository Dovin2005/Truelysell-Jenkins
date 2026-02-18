<?php

use Illuminate\Support\Facades\Route;
use Modules\Testimonials\app\Http\Controllers\TestimonialsController;

/*
 *--------------------------------------------------------------------------
 * API Routes
 *--------------------------------------------------------------------------
 *
 * Here is where you can register API routes for your application. These
 * routes are loaded by the RouteServiceProvider within a group which
 * is assigned the "api" middleware group. Enjoy building your API!
 *
*/

Route::middleware(['auth:sanctum'])->group(function () {
    Route::group(['prefix' => 'admin', 'middleware' => 'api'], function() {
        Route::post('/testimonial-list', [TestimonialsController::class, 'index']);
        Route::post('/save-testimonial', [TestimonialsController::class, 'store']);
        Route::post('/delete-testimonial', [TestimonialsController::class, 'destroy']);
        Route::post('/change-status-testimonial', [TestimonialsController::class, 'statusChange']);
    });
});
