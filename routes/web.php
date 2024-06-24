<?php

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

Route::any('/', function () {
    return view('welcome');
})->withoutMiddleware([VerifyCsrfToken::class]);
