<?php

namespace RaDevs\JwtAuth\Listeners;

use Illuminate\Support\Facades\Log;
use RaDevs\JwtAuth\Events\UserLoggedIn;
use RaDevs\JwtAuth\Events\UserLoginFailed;
use RaDevs\JwtAuth\Events\UserRegistered;
use RaDevs\JwtAuth\Events\UserLoggedOut;
use RaDevs\JwtAuth\Events\PasswordResetRequested;
use RaDevs\JwtAuth\Events\PasswordResetCompleted;

class LogSecurityEvent
{
    public function handleUserLoggedIn(UserLoggedIn $event): void
    {
        Log::info('User logged in', [
            'user_id' => $event->user->id ?? null,
            'email' => $event->user->email ?? null,
            'ip_address' => $event->ipAddress,
            'user_agent' => $event->userAgent,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    public function handleUserLoginFailed(UserLoginFailed $event): void
    {
        Log::warning('User login failed', [
            'email' => $event->email,
            'reason' => $event->reason,
            'ip_address' => $event->ipAddress,
            'user_agent' => $event->userAgent,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    public function handleUserRegistered(UserRegistered $event): void
    {
        Log::info('User registered', [
            'user_id' => $event->user->id ?? null,
            'email' => $event->user->email ?? null,
            'ip_address' => $event->ipAddress,
            'user_agent' => $event->userAgent,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    public function handleUserLoggedOut(UserLoggedOut $event): void
    {
        Log::info('User logged out', [
            'user_id' => $event->user->id ?? null,
            'email' => $event->user->email ?? null,
            'ip_address' => $event->ipAddress,
            'user_agent' => $event->userAgent,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    public function handlePasswordResetRequested(PasswordResetRequested $event): void
    {
        Log::info('Password reset requested', [
            'email' => $event->email,
            'ip_address' => $event->ipAddress,
            'user_agent' => $event->userAgent,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    public function handlePasswordResetCompleted(PasswordResetCompleted $event): void
    {
        Log::info('Password reset completed', [
            'user_id' => $event->user->id ?? null,
            'email' => $event->user->email ?? null,
            'ip_address' => $event->ipAddress,
            'user_agent' => $event->userAgent,
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
