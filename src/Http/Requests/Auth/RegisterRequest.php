<?php

namespace RaDevs\JwtAuth\Http\Requests\Auth;


use Illuminate\Validation\Rules\Password;
use RaDevs\JwtAuth\Http\Requests\BaseApiRequest;


class RegisterRequest extends BaseApiRequest
{
    public function rules(): array
    {
        $userTable = (new (config('ra-jwt-auth.classes.user_model')))->getTable();
        $configFields = config('ra-jwt-auth.registration.fields', []);

        // Если в конфиге есть поля, используем их, но заменяем плейсхолдеры
        if (!empty($configFields)) {
            $rules = [];
            foreach ($configFields as $field => $rule) {
                // Заменяем плейсхолдер {user_table} на реальное имя таблицы
                if (is_string($rule)) {
                    $rule = str_replace('{user_table}', $userTable, $rule);
                } elseif (is_array($rule)) {
                    $rule = array_map(function ($r) use ($userTable) {
                        return is_string($r) ? str_replace('{user_table}', $userTable, $r) : $r;
                    }, $rule);
                }
                $rules[$field] = $rule;
            }
            return $rules;
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
        $nameMaxLength = config('ra-jwt-auth.validation.name.max_length', 255);
        $phonePattern = config('ra-jwt-auth.validation.phone.pattern', '/^\+\d{10,15}$/');

        return [
            'email' => 'required|email:rfc,dns|unique:'.$userTable.',email|max:'.$emailMaxLength,
            'name' => 'required|string|max:'.$nameMaxLength,
            'last_name' => 'required|string|max:'.$nameMaxLength,
            'phone' => 'required|string|regex:'.$phonePattern,
            'password' => ['required','confirmed', $passwordRules],
            'password_confirmation' => 'required_with:password',
        ];
    }

    /**
     * Подготовка данных для создания пользователя.
     * Исключает служебные поля (например, password_confirmation).
     *
     * @return array
     */
    public function getRegistrationData(): array
    {
        $data = $this->validated();
        $excludeFields = config('ra-jwt-auth.registration.exclude_from_create', ['password_confirmation']);

        // Исключаем служебные поля
        foreach ($excludeFields as $field) {
            unset($data[$field]);
        }

        return $data;
    }
}