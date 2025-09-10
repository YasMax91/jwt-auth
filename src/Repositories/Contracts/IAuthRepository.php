<?php

namespace RaDevs\JwtAuth\Repositories\Contracts;


use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\JsonResponse;


interface IAuthRepository
{
    public function attempt(array $credentials): bool|string;
    public function getAuthenticatedUserRaw(): ?Authenticatable;
    public function logout(): void;
    public function refreshToken(): bool|string;
    public function checkRefreshToken(string $refreshToken);
    public function getExpiresIn(): int;
    public function respondWithToken(string $token): array;
    public function responseWithToken(string $token, string $message): JsonResponse;
}