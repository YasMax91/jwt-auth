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
        $configFields = config('ra-jwt-auth.password_reset.fields', []);
        $codeLength = config('ra-jwt-auth.password_reset.code_length', 8);
        $codeAlphabet = config('ra-jwt-auth.password_reset.code_alphabet', 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789');
        $codeAlphabetEscaped = preg_quote($codeAlphabet, '/');

        // Если в конфиге есть поля, используем их, но обновляем правило для code
        if (!empty($configFields)) {
            // Динамически обновляем правило валидации кода на основе длины и алфавита из конфига
            $configFields['code'] = [
                'required',
                'string',
                "size:{$codeLength}",
                "regex:/^[{$codeAlphabetEscaped}]{{$codeLength}}$/",
            ];
            return $configFields;
        }

        // Fallback на дефолтные правила, если конфиг не настроен
        $passwordMinLength = config('ra-jwt-auth.validation.password.min_length', 8);
        $passwordRules = Password::min($passwordMinLength);
        
        if (config('ra-jwt-auth.validation.password.require_letters', true)) {
            $passwordRules = $passwordRules->letters();
        }
        if (config('ra-jwt-auth.validation.password.require_mixed_case', true)) {
            $passwordRules = $passwordRules->mixedCase();
        }
        if (config('ra-jwt-auth.validation.password.require_numbers', true)) {
            $passwordRules = $passwordRules->numbers();
        }
        if (config('ra-jwt-auth.validation.password.require_symbols', true)) {
            $passwordRules = $passwordRules->symbols();
        }

        $emailMaxLength = config('ra-jwt-auth.validation.email.max_length', 255);

        return [
            'email' => 'required|email|max:'.$emailMaxLength,
            // Код использует алфавит и длину из конфига
            'code' => [
                'required',
                'string',
                "size:{$codeLength}",
                "regex:/^[{$codeAlphabetEscaped}]{{$codeLength}}$/",
            ],
            'password' => [
                'required',
                'confirmed',
                $passwordRules,
            ],
            'password_confirmation' => ['required'],
        ];
    }

    /**
     * Подготовка данных для обновления пароля.
     * Исключает служебные поля (например, password_confirmation, code).
     *
     * @return array
     */
    public function getResetData(): array
    {
        $data = $this->validated();
        $excludeFields = config('ra-jwt-auth.password_reset.exclude_from_update', ['password_confirmation', 'code']);

        // Исключаем служебные поля
        foreach ($excludeFields as $field) {
            unset($data[$field]);
        }

        return $data;
    }
}