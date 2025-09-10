<?php

namespace RaDevs\ApiJsonResponse\Facades;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Facade;
use Symfony\Component\HttpFoundation\Response;

/**
 * @method static JsonResponse success(array $data, string $message = "", int $code = Response::HTTP_OK, ?string $cookies = null)
 * @method static JsonResponse error(string $message = "", int $code = Response::HTTP_NOT_FOUND, ?array $trace = null)
 * @method static JsonResponse fail(array $errors, string $message = "")
 * @method static string generateMessage(string $method, string $source)
 */
class ApiJsonResponse extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'api-json-response';
    }
}
