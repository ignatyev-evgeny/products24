<?php

use App\Http\Controllers\Integration\ProductController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

Route::any('/', [ProductController::class, 'index'])
    ->withoutMiddleware([VerifyCsrfToken::class])
    ->name('index');

Route::any('/product/list/{integration}/{deal}', [ProductController::class, 'productList'])
    ->withoutMiddleware([VerifyCsrfToken::class])
    ->name('productList');

Route::get('/product/{integration}', [ProductController::class, 'getProduct'])
    ->withoutMiddleware([VerifyCsrfToken::class])
    ->middleware(['CacheResponse'])
    ->name('getProduct');

Route::get('/product-item/{integration}', [ProductController::class, 'getProductItem'])
    ->withoutMiddleware([VerifyCsrfToken::class])
    ->middleware(['CacheResponse'])
    ->name('getProductItem');
