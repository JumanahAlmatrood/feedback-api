<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FeedbackController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('feedback/stats', [FeedbackController::class, 'stats']);

Route::apiResource('feedback', FeedbackController::class)->except('update');