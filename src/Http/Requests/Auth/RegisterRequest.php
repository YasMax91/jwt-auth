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
        return [
            'email' => 'required|email:rfc,dns|unique:'.$userTable.',email',
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|regex:/^\+\d{10,15}$/',
            'password' => ['required','confirmed', Password::min(8)->letters()->mixedCase()->numbers()->symbols()],
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