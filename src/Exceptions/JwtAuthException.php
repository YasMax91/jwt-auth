<?php

namespace RaDevs\JwtAuth\Exceptions;

use Exception;

abstract class JwtAuthException extends Exception
{
    protected int $statusCode = 400;
    protected string $errorCode = 'JWT_AUTH_ERROR';

    public function __construct(string $message = '', ?int $statusCode = null, ?string $errorCode = null)
    {
        parent::__construct($message);

        if ($statusCode !== null) {
            $this->statusCode = $statusCode;
        }

        if ($errorCode !== null) {
            $this->errorCode = $errorCode;
        }
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }
}
