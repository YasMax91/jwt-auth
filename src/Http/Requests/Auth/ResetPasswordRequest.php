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

        // Если в конфиге есть поля, используем их, но обновляем правило для code
        if (!empty($configFields)) {
            // Динамически обновляем правило валидации кода на основе длины из конфига
            $configFields['code'] = [
                'required',
                'string',
                "size:{$codeLength}",
                "regex:/^[ABCDEFGHJKLMNPQRSTUVWXYZ2-9]{{$codeLength}}$/",
            ];
            return $configFields;
        }

        // Fallback на дефолтные правила, если конфиг не настроен
        return [
            'email' => 'required|email|max:255',
            // Allow A-Z except I,O + digits 2-9; длина берется из конфига
            'code' => [
                'required',
                'string',
                "size:{$codeLength}",
                "regex:/^[ABCDEFGHJKLMNPQRSTUVWXYZ2-9]{{$codeLength}}$/",
            ],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
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