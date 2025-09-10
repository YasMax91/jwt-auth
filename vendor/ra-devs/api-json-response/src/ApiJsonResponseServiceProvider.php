<?php

namespace RaDevs\ApiJsonResponse;

use Illuminate\Support\ServiceProvider;

class ApiJsonResponseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('api-json-response', function () {
            return new ApiJsonResponseManager();
        });
    }

    public function boot(): void
    {
    }
}
