<?php

namespace RaDevs\JwtAuth\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use RaDevs\ApiJsonResponse\Facades\ApiJsonResponse;
use Throwable;

class Handler extends ExceptionHandler
{
    public function render($request, Throwable $e)
    {
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
