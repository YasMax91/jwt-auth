<?php

namespace RaDevs\JwtAuth\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use RaDevs\JwtAuth\Events\UserLoggedIn;
use RaDevs\JwtAuth\Events\UserLoginFailed;
use RaDevs\JwtAuth\Events\UserRegistered;
use RaDevs\JwtAuth\Events\UserLoggedOut;
use RaDevs\JwtAuth\Events\PasswordResetRequested;
use RaDevs\JwtAuth\Events\PasswordResetCompleted;
use RaDevs\JwtAuth\Listeners\LogSecurityEvent;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        UserLoggedIn::class => [
            [LogSecurityEvent::class, 'handleUserLoggedIn'],
        ],
        UserLoginFailed::class => [
            [LogSecurityEvent::class, 'handleUserLoginFailed'],
        ],
        UserRegistered::class => [
            [LogSecurityEvent::class, 'handleUserRegistered'],
        ],
        UserLoggedOut::class => [
            [LogSecurityEvent::class, 'handleUserLoggedOut'],
        ],
        PasswordResetRequested::class => [
            [LogSecurityEvent::class, 'handlePasswordResetRequested'],
        ],
        PasswordResetCompleted::class => [
            [LogSecurityEvent::class, 'handlePasswordResetCompleted'],
        ],
    ];

    public function boot(): void
    {
        parent::boot();
    }
}
