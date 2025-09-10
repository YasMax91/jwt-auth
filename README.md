# ra-devs/jwt-auth

**Universal JWT authentication package for Laravel**, built on top of  
[`tymon/jwt-auth`](https://github.com/tymondesigns/jwt-auth) + [`ra-devs/api-json-response`](https://github.com/YasMax91/api-json-response).

- 🔑 Login / Me / Logout / Refresh  
- 📝 Register  
- 🔒 Password reset via **8-char alphanumeric code** (A–Z, 2–9)  
- ⚡ Configurable **User model, Resource, Repositories, Notification**  
- 📦 Publishable config, migrations, routes, views  
- 🔌 Optional integrations (Cart, roles, etc.)  
- 🛡 Works with Laravel 10/11/12+

---

## Installation

1. **Require dependencies**:
```bash
composer require tymon/jwt-auth "^2.0"
composer require ra-devs/api-json-response "^1.0"
composer require ra-devs/jwt-auth:dev-main
```

2. **Publish resources**:
```bash
php artisan vendor:publish --provider="RaDevs\JwtAuth\Providers\JwtAuthServiceProvider" --tag=ra-jwt-auth-config
php artisan vendor:publish --provider="RaDevs\JwtAuth\Providers\JwtAuthServiceProvider" --tag=ra-jwt-auth-migrations
php artisan vendor:publish --provider="RaDevs\JwtAuth\Providers\JwtAuthServiceProvider" --tag=ra-jwt-auth-views
```

3. **Publish jwt-auth config & generate secret**:
```bash
php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
php artisan jwt:secret
```

4. **Configure auth guard** in `config/auth.php`:
```php
'defaults' => [
    'guard' => 'api',
    'passwords' => 'users',
],

'guards' => [
    'api' => [
        'driver' => 'jwt',
        'provider' => 'users',
    ],
],

'providers' => [
    'users' => [
        'driver' => 'eloquent',
        'model' => App\Models\User::class,
    ],
],
```

5. **Add `refresh_token` column** to `users` table:
```php
Schema::table('users', function (Blueprint $table) {
    if (!Schema::hasColumn('users', 'refresh_token')) {
        $table->string('refresh_token')->nullable()->after('remember_token');
        $table->index('refresh_token');
    }
});
```

6. **Run migrations**:
```bash
php artisan migrate
```

---

## Endpoints

Default prefix: `/api/auth`

| Method | Endpoint              | Description                              | Auth |
|--------|-----------------------|------------------------------------------|------|
| POST   | `/login`              | Authenticate user                        | –    |
| POST   | `/register`           | Register a new user                      | –    |
| GET    | `/me`                 | Get current user                         | ✔    |
| POST   | `/logout`             | Logout + clear refresh token             | ✔    |
| POST   | `/refresh`            | Refresh access token via cookie          | ✔    |
| POST   | `/forgot-password`    | Send reset code (8 chars)                | –    |
| POST   | `/can-reset-password` | Check if reset code is valid             | –    |
| POST   | `/reset-password`     | Reset password by code                   | –    |

---

## Configuration

After publishing, edit `config/ra-jwt-auth.php`.

### Override classes
```php
'classes' => [
    'user_model' => App\Models\User::class,
    'user_resource' => App\Http\Resources\UserResource::class,
    'auth_repository_interface' => RaDevs\JwtAuth\Repositories\Contracts\IAuthRepository::class,
    'auth_repository' => App\Repositories\CustomAuthRepository::class,
    'user_repository_interface' => RaDevs\JwtAuth\Repositories\Contracts\IUserRepository::class,
    'user_repository' => App\Repositories\CustomUserRepository::class,
    'password_reset_service' => RaDevs\JwtAuth\Services\PasswordResetCodeService::class,
    'notification' => App\Notifications\CustomResetPasswordNotification::class,
],
```

### Refresh token cookie
```php
'refresh_cookie' => [
    'name' => 'refresh_token',
    'minutes' => 1440, // 1 day
    'secure' => null,  // true/false or null (default)
    'http_only' => true,
    'same_site' => null, // lax|strict|none|null
    'path' => '/',
    'domain' => null,
],
```

---

## Responses

Все ответы стандартизированы через [`ra-devs/api-json-response`](https://github.com/YasMax91/api-json-response).

Пример:
```json
{
  "success": true,
  "status": "success",
  "message": "The user has been successfully logged in",
  "data": {
    "user": {
      "id": 1,
      "name": "John",
      "email": "john@example.com"
    },
    "token": {
      "access_token": "eyJ0eXAiOiJKV1QiLCJhbGci...",
      "token_type": "bearer",
      "expires_in": 3600
    }
  }
}
```

---

## Customization

- Подмени `UserResource` → свой  
- Подмени `Repositories` → бизнес-логика, роли, и т.п.  
- Подмени `Notification` → своё письмо (view или Markdown)  
- Измени prefix роутов в `config/ra-jwt-auth.php`  

---

## Development

Локально подключить через `path`:
```json
"repositories": [
  { "type": "path", "url": "../jwt-auth", "options": { "symlink": true } }
]
```
```bash
composer require ra-devs/jwt-auth:"*@dev"
```

---

## License
MIT © RA Devs
