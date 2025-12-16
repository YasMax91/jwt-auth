<?php

namespace RaDevs\JwtAuth\Http\Controllers;


use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use RaDevs\JwtAuth\Repositories\Contracts\IAuthRepository;
use RaDevs\JwtAuth\Repositories\Contracts\IUserRepository;
use RaDevs\JwtAuth\Http\Requests\Auth\LoginRequest;
use RaDevs\JwtAuth\Http\Requests\Auth\RegisterRequest;
use RaDevs\JwtAuth\Http\Requests\Auth\ForgotRequest;
use RaDevs\JwtAuth\Http\Requests\Auth\CanResetPasswordRequest;
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


        $user = $userRepository->getActivatedUserByField('email', $credentials['email']);
        if (!$user) {
            return ApiJsonResponse::error('The user with email address you entered does not exist.', 404);
        }

        if (!$token = $this->authRepository->attempt($credentials)) {
            return ApiJsonResponse::error('Invalid credentials', 401);
        }

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
        $this->authRepository->logout();

        return ApiJsonResponse::success([], 'The user has been successfully logged out');
    }


    public function refresh(RefreshTokenRequest $request): JsonResponse
    {
        $cookieName = config('ra-jwt-auth.refresh_cookie.name');
        $token = $request->cookie($cookieName);

        if (!$token) {
            return ApiJsonResponse::error('Token Invalid', Response::HTTP_FORBIDDEN);
        }

        $user = $this->authRepository->checkRefreshToken($token);
        if (!$user) {
            return ApiJsonResponse::error('Token Invalid', Response::HTTP_FORBIDDEN);
        }

        auth('api')->login($user);

        return $this->authRepository->responseWithToken(
            $this->authRepository->refreshToken(),
            "The user's token has been successfully refreshed"
        );
    }


    public function register(RegisterRequest $request, IUserRepository $userRepository): JsonResponse
    {
        $data = $request->validated();
        $user = $userRepository->create($data);

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


        try {
            $this->passwordResetCodeService->issueCode(
                email: $email,
                ipAddress: $request->ip(),
                userAgent: (string) $request->userAgent(),
                ttlMinutes: $ttlMinutes,
            );
        } catch (ValidationException $e) {
            return ApiJsonResponse::error($e->getMessage(), 429);
        }

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
            ApiJsonResponse::error('The password reset code has expired or is invalid', 400);
        }

        // Ensure user exists (service will also validate, but this gives clearer 404)
        $userModel = config('ra-jwt-auth.classes.user_model');
        $user = $userModel::query()->where('email', mb_strtolower($data['email']))->first();
        if (!$user) {
            return ApiJsonResponse::error('User with current email does not exist', 404);
        }

        try {
            $this->passwordResetCodeService->resetPassword(
                email: $data['email'],
                code: $data['code'],
                newPassword: $data['password'],
            );
        } catch (ValidationException $validationException) {
            return ApiJsonResponse::error($validationException->getMessage(), 400);
        }

        return ApiJsonResponse::success([], 'Password reset was successful!');
    }
}