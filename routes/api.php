<?php

use Illuminate\Support\Facades\Route;
use RaDevs\JwtAuth\Http\Controllers\AuthController;

Route::prefix(config('ra-jwt-auth.route'))->group(function () {
    Route::controller(AuthController::class)->group(function () {
        Route::middleware('auth:api')->group(function () {
            Route::post('logout', 'logout');
            Route::get('me', 'me');
        });
        Route::post('refresh', 'refresh');
        Route::post('login', 'login');
        Route::post('register', 'register');
        Route::post('forgot-password', 'forgot');
        Route::post('can-reset-password', 'canReset');
        Route::post('reset-password', 'reset');
    });
});