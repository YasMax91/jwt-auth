<?php

namespace RaDevs\JwtAuth\Tests;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use RaDevs\JwtAuth\Exceptions\JwtAuthException;
use RaDevs\ApiJsonResponse\Facades\ApiJsonResponse;
use Throwable;

class TestExceptionHandler extends ExceptionHandler
{
    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e)
    {
        // Handle our custom JWT auth exceptions
        if ($e instanceof JwtAuthException) {
            return ApiJsonResponse::error(
                $e->getMessage(),
                $e->getStatusCode(),
                ['error_code' => $e->getErrorCode()]
            );
        }

        return parent::render($request, $e);
    }
}
