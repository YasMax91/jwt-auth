<?php

namespace RaDevs\JwtAuth\Http\Requests\Auth;


use Illuminate\Validation\Rules\Password;
use RaDevs\JwtAuth\Http\Requests\BaseApiRequest;


class RegisterRequest extends BaseApiRequest
{
    public function rules(): array
    {
        $userTable = (new (config('ra-jwt-auth.classes.user_model')))->getTable();

        return [
            'email' => 'required|email:rfc,dns|unique:'.$userTable.',email',
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|regex:/^\+\d{10,15}$/',
            'password' => ['required','confirmed', Password::min(8)->letters()->mixedCase()->numbers()->symbols()->uncompromised()],
            'password_confirmation' => 'required_with:password',
        ];
    }
}