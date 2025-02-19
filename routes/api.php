<?php

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;

Route::get('/ping', function () {
    return new JsonResponse(['message' => 'pong']);
});
