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
        $codeLength = config('ra-jwt-auth.password_reset.code_length', 8);
        $codeAlphabet = config('ra-jwt-auth.password_reset.code_alphabet', 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789');
        $codeAlphabetEscaped = preg_quote($codeAlphabet, '/');
        $emailMaxLength = config('ra-jwt-auth.validation.email.max_length', 255);

        return [
            'email' => 'required|email|max:'.$emailMaxLength,
            'code' => [
                'required',
                'string',
                "size:{$codeLength}",
                "regex:/^[{$codeAlphabetEscaped}]{{$codeLength}}$/",
            ],
        ];
    }
}