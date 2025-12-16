<?php

namespace RaDevs\JwtAuth\Exceptions;

class PasswordResetException extends JwtAuthException
{
    protected int $statusCode = 400;
    protected string $errorCode = 'PASSWORD_RESET_ERROR';

    public static function codeExpired(): self
    {
        return new self('The password reset code has expired', 400, 'PASSWORD_RESET_CODE_EXPIRED');
    }

    public static function codeInvalid(): self
    {
        return new self('Invalid password reset code', 400, 'PASSWORD_RESET_CODE_INVALID');
    }

    public static function tooManyAttempts(): self
    {
        return new self('Too many attempts. Please request a new code', 429, 'PASSWORD_RESET_TOO_MANY_ATTEMPTS');
    }

    public static function rateLimited(): self
    {
        return new self('Please wait before requesting another code', 429, 'PASSWORD_RESET_RATE_LIMITED');
    }
}
