<?php

namespace RaDevs\JwtAuth\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use RaDevs\JwtAuth\Providers\JwtAuthServiceProvider;
use Tymon\JWTAuth\Providers\LaravelServiceProvider as JWTServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->artisan('migrate', ['--database' => 'testing'])->run();
    }

    protected function getPackageProviders($app): array
    {
        return [
            JwtAuthServiceProvider::class,
            JWTServiceProvider::class,
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
