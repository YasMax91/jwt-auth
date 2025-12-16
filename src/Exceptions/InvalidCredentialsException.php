<?php

namespace RaDevs\JwtAuth\Exceptions;

class InvalidCredentialsException extends JwtAuthException
{
    protected int $statusCode = 401;
    protected string $errorCode = 'INVALID_CREDENTIALS';

    public function __construct(string $message = 'Invalid credentials provided')
    {
        parent::__construct($message, $this->statusCode, $this->errorCode);
    }
}
