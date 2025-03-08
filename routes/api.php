<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Api\BotController;
use App\Http\Controllers\Api\SourceController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::get('/ping', fn() => response()->json(['message' => 'pong']));

    // Bots routes
    Route::apiResource('bots', BotController::class);
    Route::apiResource('bots.sources', SourceController::class);
    Route::get('bots/{bot}/sources/{source}/status', [SourceController::class, 'status']);

    Route::post('/sources', [SourceController::class, 'store']);
    Route::get('/sources/{source}', [SourceController::class, 'show']);
    Route::post('/sources/{source}/refresh', [SourceController::class, 'refresh']);
});
