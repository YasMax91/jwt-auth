<?php

namespace RaDevs\ApiJsonResponse;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class ApiJsonResponseManager
{
    public function success(
        array $data,
        string $message = "",
        int $code = ResponseAlias::HTTP_OK,
        ?string $cookies = null
    ): JsonResponse {
        $response = new ResponseDTO(true, $message, $code, $data);

        return !$cookies
            ? response()->json($response->getResponse(), $code)
            : response()->json($response->getResponse(), $code)
                ->cookie('refresh_token', $cookies, 60 * 24); // 1440 хв = 1 доба
    }

    public function error(
        string $message = "",
        int $code = ResponseAlias::HTTP_NOT_FOUND,
        ?array $trace = null,
    ): JsonResponse {
        $response = new ResponseDTO(false, $message, $code, [], [], $trace);

        return response()->json($response->getResponse(), $code);
    }

    public function fail(
        array $errors,
        string $message = "",
    ): JsonResponse {
        $code = ResponseAlias::HTTP_UNPROCESSABLE_ENTITY;
        $response = new ResponseDTO(false, $message, $code, [], $errors);

        return response()->json($response->getResponse(), $code);
    }

    public function generateMessage(string $method, string $source): string
    {
        $action = match ($method) {
            'index', 'show' => 'fetched',
            'store' => 'created',
            'update' => 'updated',
            'register' => 'registered',
            default => ':action'
        };

        $target = match ($method) {
            'index' => Str::plural($source),
            'show', 'store', 'update', 'register' => Str::singular($source),
            default => ':source'
        };

        $isPlural = $this->isPlural($target);
        $verb = $isPlural ? 'have' : 'has';

        return "The $target $verb been successfully $action";
    }

    private function isPlural(string $string): bool
    {
        return Str::plural($string) === $string;
    }
}
