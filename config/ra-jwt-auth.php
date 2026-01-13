<?php


return [
    'route' => [
        'prefix' => 'api/auth',
        'middleware' => ['api'],
    ],

    'refresh_cookie' => [
        'name' => 'refresh_token',
        'minutes' => 60 * 24, // 1 day
        'secure' => null, // null = keep Laravel default; true/false to force
        'http_only' => true,
        'same_site' => null, // 'lax'|'strict'|'none'|null
        'path' => '/',
        'domain' => null,
    ],

    'classes' => [
        'user_model' => "App\Models\User",
        'user_resource' => RaDevs\JwtAuth\Http\Resources\UserResource::class,
        'auth_repository_interface' => RaDevs\JwtAuth\Repositories\Contracts\IAuthRepository::class,
        'auth_repository' => RaDevs\JwtAuth\Repositories\AuthRepository::class,
        'user_repository_interface' => RaDevs\JwtAuth\Repositories\Contracts\IUserRepository::class,
        'user_repository' => RaDevs\JwtAuth\Repositories\UserRepository::class,
        'password_reset_service' => RaDevs\JwtAuth\Services\PasswordResetCodeService::class,
        'notification' => RaDevs\JwtAuth\Notifications\ApiResetPasswordCodeNotification::class,
    ],

    'registration' => [
        // Поля, которые будут использоваться при регистрации
        // Можно переопределить в своем RegisterRequest
        // {user_table} будет автоматически заменен на реальное имя таблицы
        // 
        // ВАЖНО: Для сложных правил (например, с объектами Password) 
        // рекомендуется расширять RegisterRequest вместо изменения конфига
        'fields' => [
            'email' => 'required|email:rfc,dns|unique:{user_table},email',
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|regex:/^\+\d{10,15}$/',
            'password' => 'required|confirmed|min:8',
            'password_confirmation' => 'required_with:password',
        ],
        // Поля, которые нужно исключить при создании пользователя
        // (например, password_confirmation не должно попадать в БД)
        'exclude_from_create' => [
            'password_confirmation',
        ],
    ],

    'login' => [
        // Поля, по которым можно искать пользователя при входе
        // Система будет пробовать найти пользователя по каждому полю по очереди
        'search_fields' => ['email'],
        // Правила валидации для полей входа
        // Одно из полей из search_fields должно быть заполнено
        'fields' => [
            'email' => 'required_without_all:username,phone|email',
            'username' => 'required_without_all:email,phone|string|max:255',
            'phone' => 'required_without_all:email,username|string|regex:/^\+\d{10,15}$/',
            'password' => 'required|string|min:6',
        ],
    ],

    'password_reset' => [
        // Длина кода для сброса пароля
        'code_length' => 8,
        // Правила валидации для сброса пароля
        'fields' => [
            'email' => 'required|email|max:255',
            'code' => 'required|string|size:8|regex:/^[ABCDEFGHJKLMNPQRSTUVWXYZ2-9]{8}$/',
            'password' => 'required|confirmed|min:8',
            'password_confirmation' => 'required_with:password',
        ],
        // Поля, которые нужно исключить при обновлении пароля
        'exclude_from_update' => [
            'password_confirmation',
            'code',
        ],
    ],

    'forgot_password' => [
        // Правила валидации для запроса сброса пароля
        'fields' => [
            'email' => 'required|email|max:255',
        ],
    ],
];