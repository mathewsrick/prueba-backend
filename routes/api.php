<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CurrencyController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductPriceController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:6,1');
    Route::post('register', [AuthController::class, 'register'])->middleware('throttle:6,1');
    Route::middleware('auth:sanctum')->post('logout', [AuthController::class, 'logout']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('products', ProductController::class);
    Route::apiResource('products.prices', ProductPriceController::class)->only(['index', 'store']);
    Route::apiResource('currencies', CurrencyController::class);
});
