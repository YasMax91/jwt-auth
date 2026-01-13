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
        // Publish configuration
        $configPath = realpath(__DIR__.'/../../config/ra-jwt-auth.php');
        $this->publishes([
            $configPath => config_path('ra-jwt-auth.php'),
        ], 'ra-jwt-auth-config');

        // Publish views
        $viewsPath = realpath(__DIR__.'/../../resources/views') ?: __DIR__.'/../../resources/views';
        $this->publishes([
            $viewsPath => resource_path('views/vendor/ra-jwt-auth'),
        ], 'ra-jwt-auth-views');
        
        $this->loadViewsFrom($viewsPath, 'ra-jwt-auth');

        // Publish migrations
        $migrationsPath = realpath(__DIR__.'/../../database/migrations') ?: __DIR__.'/../../database/migrations';
        $this->publishesMigrations([
            $migrationsPath => database_path('migrations'),
        ], 'ra-jwt-auth-migrations');

        // Load routes
        $routesPath = realpath(__DIR__.'/../../routes/api.php') ?: __DIR__.'/../../routes/api.php';
        $this->loadRoutesFrom($routesPath);
    }
}