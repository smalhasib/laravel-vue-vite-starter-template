<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('app');
});

// This should be the last route in web.php
Route::get('{any}', function () {
    return view('app');
})->where('any', '.*');
