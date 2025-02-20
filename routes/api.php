<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BotController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::get('/ping', fn () => response()->json(['message' => 'pong']));

    // Bots routes
    Route::apiResource('bots', BotController::class);
});
