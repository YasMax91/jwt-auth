<?php

namespace RaDevs\JwtAuth\Repositories;


use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use RaDevs\ApiJsonResponse\Facades\ApiJsonResponse;
use RaDevs\JwtAuth\Repositories\Contracts\IAuthRepository;


class AuthRepository implements IAuthRepository
{
    public function attempt(array $credentials): bool|string
    {
        return auth('api')->attempt($credentials);
    }


    public function getAuthenticatedUserRaw(): ?Authenticatable
    {
        return auth('api')->user();
    }


    public function logout(): void
    {
        $cookieName = config('ra-jwt-auth.refresh_cookie.name');
        Cookie::queue(Cookie::forget($cookieName));
        $this->updateUserRefreshToken(false);
        auth('api')->logout(true);
    }


    public function refreshToken(): bool|string
    {
        return auth('api')->refresh();
    }


    public function checkRefreshToken(string $refreshToken)
    {
        $userModel = config('ra-jwt-auth.classes.user_model');
        return $userModel::where('refresh_token', $refreshToken)->first();
    }


    public function getExpiresIn(): int
    {
        return auth('api')->factory()->getTTL();
    }


    public function respondWithToken(string $token): array
    {
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $this->getExpiresIn(),
        ];
    }

    public function responseWithToken(string $token, string $message): JsonResponse
    {
        $user = $this->getAuthenticatedUserRaw();


        $resourceClass = config('ra-jwt-auth.classes.user_resource');
        $data = [
            'user' => new $resourceClass($user),
            'token' => $this->respondWithToken($token),
        ];


        $refreshToken = $this->updateUserRefreshToken();
        $cookieConfig = config('ra-jwt-auth.refresh_cookie');


        $response = ApiJsonResponse::success($data, $message, 200);

        return $response->cookie(
            $cookieConfig['name'],
            $refreshToken,
            $cookieConfig['minutes'],
            $cookieConfig['path'],
            $cookieConfig['domain'],
            $cookieConfig['secure'],
            $cookieConfig['http_only'],
            false,
            $cookieConfig['same_site']
        );
    }


    private function generateRefreshToken(): string
    {
        return Str::uuid()->toString();
    }


    private function updateUserRefreshToken(bool $refresh = true): ?string
    {
        $user = $this->getAuthenticatedUserRaw();
        if (!$user) return null;

        $refreshToken = $refresh ? $this->generateRefreshToken() : null;
        $user->forceFill(['refresh_token' => $refreshToken])->save();


        return $refreshToken;
    }
}