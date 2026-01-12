<?php

namespace RaDevs\JwtAuth\Http\Requests\Auth;

use RaDevs\JwtAuth\Http\Requests\BaseApiRequest;

class ForgotRequest extends BaseApiRequest
{
    public function rules(): array
    {
        $configFields = config('ra-jwt-auth.forgot_password.fields', []);

        // Если в конфиге есть поля, используем их
        if (!empty($configFields)) {
            return $configFields;
        }

        // Fallback на дефолтные правила, если конфиг не настроен
        return [
            'email' => 'required|email|max:255',
        ];
    }
}