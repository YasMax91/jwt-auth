<?php


return [
    'route' => [
        'prefix' => 'api/auth',
        'middleware' => ['api'],
    ],

    'refresh_cookie' => [
        'name' => env('RA_JWT_AUTH_REFRESH_COOKIE_NAME', 'refresh_token'),
        'minutes' => env('RA_JWT_AUTH_REFRESH_COOKIE_MINUTES', 60 * 24), // 1 day
        'secure' => env('RA_JWT_AUTH_REFRESH_COOKIE_SECURE', null), // null = keep Laravel default; true/false to force
        'http_only' => env('RA_JWT_AUTH_REFRESH_COOKIE_HTTP_ONLY', true),
        'same_site' => env('RA_JWT_AUTH_REFRESH_COOKIE_SAME_SITE', null), // 'lax'|'strict'|'none'|null
        'path' => env('RA_JWT_AUTH_REFRESH_COOKIE_PATH', '/'),
        'domain' => env('RA_JWT_AUTH_REFRESH_COOKIE_DOMAIN', null),
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
        'code_length' => env('RA_JWT_AUTH_PASSWORD_RESET_CODE_LENGTH', 8),
        // Алфавит для генерации кода (без I, O, 0, 1 для избежания путаницы)
        'code_alphabet' => env('RA_JWT_AUTH_PASSWORD_RESET_CODE_ALPHABET', 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'),
        // Максимальное количество попыток ввода кода
        'max_attempts' => env('RA_JWT_AUTH_PASSWORD_RESET_MAX_ATTEMPTS', 5),
        // Минимальный интервал между запросами кода (в секундах)
        'rate_limit_seconds' => env('RA_JWT_AUTH_PASSWORD_RESET_RATE_LIMIT_SECONDS', 60),
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

    'rate_limits' => [
        // Rate limits для эндпоинтов (попыток в минуту)
        'login' => env('RA_JWT_AUTH_RATE_LIMIT_LOGIN', 5),
        'register' => env('RA_JWT_AUTH_RATE_LIMIT_REGISTER', 3),
        'forgot_password' => env('RA_JWT_AUTH_RATE_LIMIT_FORGOT_PASSWORD', 3),
        'reset_password' => env('RA_JWT_AUTH_RATE_LIMIT_RESET_PASSWORD', 3),
        'refresh' => env('RA_JWT_AUTH_RATE_LIMIT_REFRESH', 10),
    ],

    'validation' => [
        // Общие правила валидации
        'password' => [
            'min_length' => env('RA_JWT_AUTH_PASSWORD_MIN_LENGTH', 8),
            'min_length_login' => env('RA_JWT_AUTH_PASSWORD_MIN_LENGTH_LOGIN', 6), // Минимальная длина для логина (обычно менее строгая)
            'require_letters' => env('RA_JWT_AUTH_PASSWORD_REQUIRE_LETTERS', true),
            'require_mixed_case' => env('RA_JWT_AUTH_PASSWORD_REQUIRE_MIXED_CASE', true),
            'require_numbers' => env('RA_JWT_AUTH_PASSWORD_REQUIRE_NUMBERS', true),
            'require_symbols' => env('RA_JWT_AUTH_PASSWORD_REQUIRE_SYMBOLS', true),
        ],
        'email' => [
            'max_length' => env('RA_JWT_AUTH_EMAIL_MAX_LENGTH', 255),
        ],
        'name' => [
            'max_length' => env('RA_JWT_AUTH_NAME_MAX_LENGTH', 255),
        ],
        'phone' => [
            'pattern' => env('RA_JWT_AUTH_PHONE_PATTERN', '/^\+\d{10,15}$/'),
        ],
    ],
];