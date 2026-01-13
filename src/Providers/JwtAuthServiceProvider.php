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
        $packagePath = dirname(__DIR__, 2);
        
        // Publish configuration
        $configPath = $packagePath.'/config/ra-jwt-auth.php';
        if (file_exists($configPath)) {
            $this->publishes([
                $configPath => config_path('ra-jwt-auth.php'),
            ], 'ra-jwt-auth-config');
        }

        // Publish views
        $viewsPath = $packagePath.'/resources/views';
        if (is_dir($viewsPath)) {
            $this->publishes([
                $viewsPath => resource_path('views/vendor/ra-jwt-auth'),
            ], 'ra-jwt-auth-views');
            
            $this->loadViewsFrom($viewsPath, 'ra-jwt-auth');
        }

        // Publish migrations
        $migrationsPath = $packagePath.'/database/migrations';
        if (is_dir($migrationsPath)) {
            $this->publishesMigrations([
                $migrationsPath => database_path('migrations'),
            ], 'ra-jwt-auth-migrations');
        }

        // Load routes
        $routesPath = $packagePath.'/routes/api.php';
        if (file_exists($routesPath)) {
            $this->loadRoutesFrom($routesPath);
        }
    }
}