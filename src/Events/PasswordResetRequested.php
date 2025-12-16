<?php

namespace RaDevs\JwtAuth\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PasswordResetRequested
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly string $email,
        public readonly string $ipAddress,
        public readonly string $userAgent,
    ) {}
}
