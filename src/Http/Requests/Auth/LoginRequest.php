<?php

namespace RaDevs\JwtAuth\Http\Requests\Auth;


use RaDevs\JwtAuth\Http\Requests\BaseApiRequest;


class LoginRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ];
    }
}