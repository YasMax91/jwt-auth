<?php

namespace RaDevs\JwtAuth\Http\Requests\Auth;

use RaDevs\JwtAuth\Http\Requests\BaseApiRequest;

class RefreshTokenRequest extends BaseApiRequest
{
    public function rules(): array { return []; }
}