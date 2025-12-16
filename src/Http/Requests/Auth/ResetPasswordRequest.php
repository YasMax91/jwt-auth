<?php

namespace RaDevs\JwtAuth\Http\Requests\Auth;

use Illuminate\Validation\Rules\Password;
use RaDevs\JwtAuth\Http\Requests\BaseApiRequest;

class ResetPasswordRequest extends BaseApiRequest
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
            // Allow A-Z except I,O + digits 2-9; length = 8 (L is ALLOWED!)
            'code' => ['required', 'string', 'size:8', 'regex:/^[ABCDEFGHJKLMNPQRSTUVWXYZ2-9]{8}$/'],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
            ],
            'password_confirmation' => ['required'],
        ];
    }
}