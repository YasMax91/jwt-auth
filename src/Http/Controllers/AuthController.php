<?php

namespace RaDevs\JwtAuth\Http\Controllers;

use Illuminate\Http\JsonResponse;
use RaDevs\JwtAuth\Exceptions\InvalidCredentialsException;
use RaDevs\JwtAuth\Exceptions\InvalidTokenException;
use RaDevs\JwtAuth\Exceptions\UserNotFoundException;
use RaDevs\JwtAuth\Exceptions\PasswordResetException;
use RaDevs\JwtAuth\Events\UserLoggedIn;
use RaDevs\JwtAuth\Events\UserLoginFailed;
use RaDevs\JwtAuth\Events\UserRegistered;
use RaDevs\JwtAuth\Events\UserLoggedOut;
use RaDevs\JwtAuth\Events\PasswordResetRequested;
use RaDevs\JwtAuth\Events\PasswordResetCompleted;
use RaDevs\JwtAuth\Repositories\Contracts\IAuthRepository;
use RaDevs\JwtAuth\Repositories\Contracts\IUserRepository;
use RaDevs\JwtAuth\Http\Requests\Auth\LoginRequest;
use RaDevs\JwtAuth\Http\Requests\Auth\RegisterRequest;
use RaDevs\JwtAuth\Http\Requests\Auth\ForgotRequest;
use RaDevs\JwtAuth\Http\Requests\Auth\ResetPasswordRequest;
use RaDevs\JwtAuth\Http\Requests\Auth\RefreshTokenRequest;
use RaDevs\JwtAuth\Services\PasswordResetCodeService;
use RaDevs\JwtAuth\Http\Resources\UserResource as DefaultUserResource;
use RaDevs\ApiJsonResponse\Facades\ApiJsonResponse;


class AuthController
{
    public function __construct(
        private readonly IAuthRepository $authRepository,
        private readonly PasswordResetCodeService $passwordResetCodeService,
    ) {}


    public function login(LoginRequest $request, IUserRepository $userRepository): JsonResponse
    {
        $credentials = $request->validated();
        $loginField = $request->getLoginField();
        $loginValue = $request->getLoginValue();

        $user = $userRepository->getActivatedUserByField($loginField, $loginValue);
        if (!$user) {
            UserLoginFailed::dispatch(
                $loginValue,
                $request->ip(),
                $request->userAgent() ?? 'Unknown',
                'User not found'
            );
            throw new UserNotFoundException('The user with ' . $loginField . ' you entered does not exist');
        }

        if (!$token = $this->authRepository->attempt($credentials)) {
            UserLoginFailed::dispatch(
                $loginValue,
                $request->ip(),
                $request->userAgent() ?? 'Unknown',
                'Invalid credentials'
            );
            throw new InvalidCredentialsException();
        }

        UserLoggedIn::dispatch($user, $request->ip(), $request->userAgent() ?? 'Unknown');

        return $this->authRepository->responseWithToken($token, 'The user has been successfully logged in');
    }


    public function me(): JsonResponse
    {
        $resourceClass = config('ra-jwt-auth.classes.user_resource', DefaultUserResource::class);
        $response['user'] = new $resourceClass($this->authRepository->getAuthenticatedUserRaw());
        return ApiJsonResponse::success($response, 'The user has been successfully shown');
    }


    public function logout(): JsonResponse
    {
        $user = $this->authRepository->getAuthenticatedUserRaw();
        $this->authRepository->logout();

        if ($user) {
            UserLoggedOut::dispatch(
                $user,
                request()->ip(),
                request()->userAgent() ?? 'Unknown'
            );
        }

        return ApiJsonResponse::success([], 'The user has been successfully logged out');
    }


    public function refresh(RefreshTokenRequest $request): JsonResponse
    {
        $cookieName = config('ra-jwt-auth.refresh_cookie.name');
        $token = $request->cookie($cookieName);

        if (!$token) {
            throw new InvalidTokenException('Refresh token not found');
        }

        $user = $this->authRepository->checkRefreshToken($token);
        if (!$user) {
            throw new InvalidTokenException('Invalid refresh token');
        }

        auth('api')->login($user);

        return $this->authRepository->responseWithToken(
            $this->authRepository->refreshToken(),
            "The user's token has been successfully refreshed"
        );
    }


    public function register(RegisterRequest $request, IUserRepository $userRepository): JsonResponse
    {
        $data = $request->getRegistrationData();
        $user = $userRepository->create($data);

        UserRegistered::dispatch($user, $request->ip(), $request->userAgent() ?? 'Unknown');

        $resourceClass = config('ra-jwt-auth.classes.user_resource', DefaultUserResource::class);
        $response['user'] = new $resourceClass($user);

        return ApiJsonResponse::success($response, 'The user has been successfully registered', 201);
    }


    public function forgot(ForgotRequest $request): JsonResponse
    {
        $email = mb_strtolower(trim($request->input('email')));
        $genericMessage = 'If the email exists, the code has been sent';

        $userModel = config('ra-jwt-auth.classes.user_model');
        $userExists = $userModel::query()->where('email', $email)->exists();
        if (!$userExists) {
            return ApiJsonResponse::success([], $genericMessage);
        }

        $ttlMinutes = (int) (config('auth.passwords.' . config('auth.defaults.passwords') . '.expire') ?? 60);

        PasswordResetRequested::dispatch($email, $request->ip(), $request->userAgent() ?? 'Unknown');

        $this->passwordResetCodeService->issueCode(
            email: $email,
            ipAddress: $request->ip(),
            userAgent: (string) $request->userAgent(),
            ttlMinutes: $ttlMinutes,
        );

        return ApiJsonResponse::success([], $genericMessage);
    }

    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        $data = $request->validated();

        $isValid = $this->passwordResetCodeService->verifyCode(
            email: $data['email'],
            code: $data['code'],
        );

        if (!$isValid) {
            throw PasswordResetException::codeInvalid();
        }

        // Ensure user exists (service will also validate, but this gives clearer 404)
        $userModel = config('ra-jwt-auth.classes.user_model');
        $user = $userModel::query()->where('email', mb_strtolower($data['email']))->first();
        if (!$user) {
            throw new UserNotFoundException('User with current email does not exist');
        }

        $this->passwordResetCodeService->resetPassword(
            email: $data['email'],
            code: $data['code'],
            newPassword: $data['password'],
        );

        PasswordResetCompleted::dispatch($user, $request->ip(), $request->userAgent() ?? 'Unknown');

        return ApiJsonResponse::success([], 'Password reset was successful!');
    }
}