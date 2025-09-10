<?php

namespace RaDevs\JwtAuth\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use JsonException;
use RaDevs\ApiJsonResponse\Facades\ApiJsonResponse;

class BaseApiRequest extends FormRequest
{
    /**
     * Handle a failed validation attempt.
     *
     * @param Validator $validator
     * @return void
     *
     * @throws JsonException
     */
    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->getMessageBag()->getMessages();

        throw new HttpResponseException(
            ApiJsonResponse::fail($errors, "Validation errors")
        );
    }
}
