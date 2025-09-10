<?php

namespace RaDevs\JwtAuth\Http\Requests\Auth;

use RaDevs\JwtAuth\Http\Requests\BaseApiRequest;

class ForgotRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [ 'email' => 'required|email|max:255' ];
    }
}