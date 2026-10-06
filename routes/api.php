<?php

use App\Http\Controllers\FeedbackController;
use Illuminate\Support\Facades\Route;

// Public
Route::get('feedback/stats', [FeedbackController::class, 'stats']);
Route::get('feedback/categories', [FeedbackController::class, 'categories']);
Route::post('feedback', [FeedbackController::class, 'store'])->middleware('throttle:10,1');

// Staff only: needs a Sanctum token
Route::middleware('auth:sanctum')->group(function () {
    Route::get('feedback/export', [FeedbackController::class, 'export'])->middleware('throttle:10,1');
    Route::delete('feedback/{feedback}', [FeedbackController::class, 'destroy']);
});

Route::apiResource('feedback', FeedbackController::class)->only(['index', 'show']);
