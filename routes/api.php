<?php

use Illuminate\Support\Facades\Route;
use RaDevs\JwtAuth\Http\Controllers\AuthController;

Route::prefix(config('ra-jwt-auth.route.prefix'))->middleware(config('ra-jwt-auth.route.middleware'))->group(function () {
    Route::controller(AuthController::class)->group(function () {
        // Authenticated routes
        Route::middleware('auth:api')->group(function () {
            Route::post('logout', 'logout');
            Route::get('me', 'me');
        });

        // Token refresh (higher limit as it's used frequently)
        Route::middleware('throttle:10,1')->group(function () {
            Route::post('refresh', 'refresh');
        });

        // Login with rate limiting (5 attempts per minute)
        Route::middleware('throttle:5,1')->group(function () {
            Route::post('login', 'login');
        });

        // Registration with rate limiting (3 attempts per minute)
        Route::middleware('throttle:3,1')->group(function () {
            Route::post('register', 'register');
        });

        // Password reset endpoints with rate limiting (3 attempts per minute)
        Route::middleware('throttle:3,1')->group(function () {
            Route::post('forgot-password', 'forgot');
            Route::post('reset-password', 'reset');
        });
    });
});