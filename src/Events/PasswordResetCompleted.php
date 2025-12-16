<?php

namespace RaDevs\JwtAuth\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PasswordResetCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly mixed $user,
        public readonly string $ipAddress,
        public readonly string $userAgent,
    ) {}
}
