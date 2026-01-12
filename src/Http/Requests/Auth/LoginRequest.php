<?php

namespace RaDevs\JwtAuth\Http\Requests\Auth;


use RaDevs\JwtAuth\Http\Requests\BaseApiRequest;


class LoginRequest extends BaseApiRequest
{
    public function rules(): array
    {
        $configFields = config('ra-jwt-auth.login.fields', []);

        // Если в конфиге есть поля, используем их
        if (!empty($configFields)) {
            return $configFields;
        }

        // Fallback на дефолтные правила, если конфиг не настроен
        return [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ];
    }

    /**
     * Получить поле, которое используется для входа (email, username, phone и т.д.)
     *
     * @return string
     */
    public function getLoginField(): string
    {
        return config('ra-jwt-auth.login.field', 'email');
    }

    /**
     * Получить значение поля входа
     *
     * @return string
     */
    public function getLoginValue(): string
    {
        $field = $this->getLoginField();
        return $this->input($field);
    }
}