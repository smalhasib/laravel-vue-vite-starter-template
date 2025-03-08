<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Api\BotController;
use App\Http\Controllers\Api\SourceController;
use App\Http\Controllers\Api\DocumentController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::get('/ping', fn() => response()->json(['message' => 'pong']));

    // Bot routes
    Route::apiResource('bots', BotController::class);

    // Source routes
    Route::prefix('bots/{bot}')->group(function () {
        Route::apiResource('sources', SourceController::class);
        Route::get('sources/{source}/status', [SourceController::class, 'status']);
    });

    Route::post('/sources', [SourceController::class, 'store']);
    Route::get('/sources/{source}', [SourceController::class, 'show']);
    Route::post('/sources/{source}/refresh', [SourceController::class, 'refresh']);

    // Document routes
    Route::post('/bots/{bot}/sources/{source}/documents', [DocumentController::class, 'store']);
    Route::delete('/bots/{bot}/sources/{source}/documents/{document}', [DocumentController::class, 'destroy']);
});
