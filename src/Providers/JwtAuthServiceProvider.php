<?php
// src/Providers/JwtAuthServiceProvider.php


namespace RaDevs\JwtAuth\Providers;


use Illuminate\Support\ServiceProvider;


class JwtAuthServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/ra-jwt-auth.php', 'ra-jwt-auth');

        $classes = config('ra-jwt-auth.classes');

        $this->app->bind($classes['auth_repository_interface'], $classes['auth_repository']);
        $this->app->bind($classes['user_repository_interface'], $classes['user_repository']);

        $this->app->alias($classes['auth_repository'], 'ra.jwt-auth.repository');
        $this->app->alias($classes['password_reset_service'], 'ra.jwt-auth.password-reset-service');

        // Register event service provider
        $this->app->register(EventServiceProvider::class);
    }


    public function boot()
    {
        $this->publishes([
            __DIR__.'/../../config/ra-jwt-auth.php' => config_path('ra-jwt-auth.php'),
        ], 'ra-jwt-auth-config');


        $this->publishes([
            __DIR__.'/../../resources/views' => resource_path('views/vendor/ra-jwt-auth'),
        ], 'ra-jwt-auth-views');


        $this->publishes([
            __DIR__.'/../../database/migrations' => database_path('migrations'),
        ], 'ra-jwt-auth-migrations');


        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'ra-jwt-auth');
        $this->loadRoutesFrom(__DIR__.'/../../routes/api.php');
    }
}