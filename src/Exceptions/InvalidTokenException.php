<?php

namespace RaDevs\JwtAuth\Exceptions;

class InvalidTokenException extends JwtAuthException
{
    protected int $statusCode = 403;
    protected string $errorCode = 'INVALID_TOKEN';

    public function __construct(string $message = 'Token is invalid or expired')
    {
        parent::__construct($message, $this->statusCode, $this->errorCode);
    }
}
