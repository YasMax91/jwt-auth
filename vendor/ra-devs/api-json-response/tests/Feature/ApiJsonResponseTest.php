<?php

use RaDevs\ApiJsonResponse\Facades\ApiJsonResponse;
use RaDevs\ApiJsonResponse\ApiJsonResponseServiceProvider;
use Symfony\Component\HttpFoundation\Response as Status;

it('returns success json', function () {
    $this->app->register(ApiJsonResponseServiceProvider::class);

    $resp = ApiJsonResponse::success(['id' => 1], 'OK', Status::HTTP_OK);
    expect($resp->getStatusCode())->toBe(Status::HTTP_OK);

    $decoded = json_decode($resp->getContent(), true);
    expect($decoded)->toMatchArray([
        'success' => true,
        'status' => 'success',
        'message' => 'OK',
        'data' => ['id' => 1],
    ]);
});

it('returns fail json', function () {
    $this->app->register(ApiJsonResponseServiceProvider::class);

    $resp = ApiJsonResponse::fail(['email' => ['The email field is required.']], 'Validation failed');
    expect($resp->getStatusCode())->toBe(Status::HTTP_UNPROCESSABLE_ENTITY);

    $decoded = json_decode($resp->getContent(), true);
    expect($decoded['status'])->toBe('fail');
    expect($decoded['errors'])->toHaveKey('email');
});

it('returns error json', function () {
    $this->app->register(ApiJsonResponseServiceProvider::class);

    $resp = ApiJsonResponse::error('Not found', Status::HTTP_NOT_FOUND);
    expect($resp->getStatusCode())->toBe(Status::HTTP_NOT_FOUND);

    $decoded = json_decode($resp->getContent(), true);
    expect($decoded['status'])->toBe('error');
});
