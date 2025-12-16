<?php

namespace RaDevs\JwtAuth\Tests;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;
use RaDevs\JwtAuth\Providers\JwtAuthServiceProvider;
use Tymon\JWTAuth\Providers\LaravelServiceProvider as JWTServiceProvider;
use RaDevs\ApiJsonResponse\ApiJsonResponseServiceProvider;
use RaDevs\JwtAuth\Exceptions\JwtAuthException;
use RaDevs\ApiJsonResponse\Facades\ApiJsonResponse;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function defineDatabaseMigrations(): void
    {
        // Create users table directly
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('last_name')->nullable();
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('password');
            $table->string('refresh_token')->nullable()->index();
            $table->rememberToken();
            $table->timestamps();
        });

        // Load package migrations (password_reset_codes table)
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    /**
     * Resolve application HTTP exception handler.
     */
    protected function resolveApplicationExceptionHandler($app): void
    {
        $app->singleton(ExceptionHandler::class, TestExceptionHandler::class);
    }

    protected function getPackageProviders($app): array
    {
        return [
            JwtAuthServiceProvider::class,
            JWTServiceProvider::class,
            ApiJsonResponseServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('auth.defaults.guard', 'api');
        $app['config']->set('auth.guards.api', [
            'driver' => 'jwt',
            'provider' => 'users',
        ]);

        $app['config']->set('auth.providers.users', [
            'driver' => 'eloquent',
            'model' => \RaDevs\JwtAuth\Tests\Fixtures\User::class,
        ]);

        // Configure package to use test user model
        $app['config']->set('ra-jwt-auth.classes.user_model', \RaDevs\JwtAuth\Tests\Fixtures\User::class);

        // Generate a 256-bit (32 character) secret for JWT
        $app['config']->set('jwt.secret', 'test-secret-key-for-jwt-auth-256bit-minimum-length-required');
        $app['config']->set('jwt.ttl', 60);
        $app['config']->set('jwt.algo', 'HS256');
    }
}
