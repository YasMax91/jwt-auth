<?php

namespace RaDevs\JwtAuth\Http\Requests\Auth;


use RaDevs\JwtAuth\Http\Requests\BaseApiRequest;

class CanResetPasswordRequest extends BaseApiRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => $this->email ? trim(mb_strtolower($this->email)) : $this->email,
            'code' => $this->code ? strtoupper(trim($this->code)) : $this->code,
        ]);
    }


    public function rules(): array
    {
        return [
            'email' => 'required|email|max:255',
            'code' => ['required','string','size:8','regex:/^[ABCDEFGHJKLMNPQRSTUVWXYZ2-9]{8}$/'],
        ];
    }
}