<?php

use App\Http\Controllers\FeedbackController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('feedback/stats', [FeedbackController::class, 'stats']);
Route::get('feedback/categories', [FeedbackController::class, 'categories']);

Route::post('feedback', [FeedbackController::class, 'store'])->middleware('throttle:10,1');

Route::apiResource('feedback', FeedbackController::class)->except(['store', 'update']);
