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
        Route::middleware('throttle:' . config('ra-jwt-auth.rate_limits.refresh', 10) . ',1')->group(function () {
            Route::post('refresh', 'refresh');
        });

        // Login with rate limiting
        Route::middleware('throttle:' . config('ra-jwt-auth.rate_limits.login', 5) . ',1')->group(function () {
            Route::post('login', 'login');
        });

        // Registration with rate limiting
        Route::middleware('throttle:' . config('ra-jwt-auth.rate_limits.register', 3) . ',1')->group(function () {
            Route::post('register', 'register');
        });

        // Password reset endpoints with rate limiting
        Route::middleware('throttle:' . config('ra-jwt-auth.rate_limits.forgot_password', 3) . ',1')->group(function () {
            Route::post('forgot-password', 'forgot');
        });
        Route::middleware('throttle:' . config('ra-jwt-auth.rate_limits.reset_password', 3) . ',1')->group(function () {
            Route::post('reset-password', 'reset');
        });
    });
});