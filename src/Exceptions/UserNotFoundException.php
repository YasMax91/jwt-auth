<?php

namespace RaDevs\JwtAuth\Exceptions;

class UserNotFoundException extends JwtAuthException
{
    protected int $statusCode = 404;
    protected string $errorCode = 'USER_NOT_FOUND';

    public function __construct(string $message = 'User not found')
    {
        parent::__construct($message, $this->statusCode, $this->errorCode);
    }
}
