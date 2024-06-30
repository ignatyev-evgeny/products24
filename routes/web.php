<?php

use App\Http\Controllers\Integration\ProductController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

Route::any('/', [ProductController::class, 'index'])
    ->withoutMiddleware([VerifyCsrfToken::class])
    ->name('index');

Route::get('/products/{integration}', [ProductController::class, 'getProducts'])
    ->withoutMiddleware([VerifyCsrfToken::class])
    ->middleware(['CacheResponse'])
    ->name('getProducts');

