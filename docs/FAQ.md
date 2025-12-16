# Frequently Asked Questions (FAQ)

## General Questions

### What is ra-devs/jwt-auth?

`ra-devs/jwt-auth` is a comprehensive JWT authentication package for Laravel that provides ready-to-use authentication endpoints, password reset functionality, security event logging, and more.

### What are the main features?

- Login / Register / Logout / Token Refresh
- Password reset via 8-character alphanumeric codes
- HTTP-only secure cookies for refresh tokens
- Rate limiting on all endpoints
- Custom exception classes with error codes
- Security event logging
- Fully customizable (models, resources, repositories)
- Comprehensive test coverage

### What Laravel versions are supported?

Laravel 10, 11, and 12+. PHP 8.1+ is required.

---

## Installation & Setup

### Do I need to install tymon/jwt-auth separately?

Yes, you need to require it as specified in the installation instructions:
```bash
composer require tymon/jwt-auth "^2.0"
```

### Why do I get "JWT secret not set" error?

You need to generate the JWT secret after publishing the config:
```bash
php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
php artisan jwt:secret
```

### Can I use a different User model?

Yes! Configure it in `config/ra-jwt-auth.php`:
```php
'classes' => [
    'user_model' => App\Models\CustomUser::class,
    // ...
]
```

### How do I customize the routes prefix?

In `config/ra-jwt-auth.php`:
```php
'route' => [
    'prefix' => 'api/v1/auth', // Change this
    'middleware' => ['api'],
]
```

---

## Authentication

### How long do access tokens last?

By default, JWT access tokens expire after 60 minutes. Configure in `config/jwt.php`:
```php
'ttl' => 60, // minutes
```

### How long do refresh tokens last?

Refresh tokens last 1 day (1440 minutes) by default. Configure in `config/ra-jwt-auth.php`:
```php
'refresh_cookie' => [
    'minutes' => 1440,
    // ...
]
```

### Why use HTTP-only cookies for refresh tokens?

HTTP-only cookies prevent XSS attacks from accessing the refresh token. This is a security best practice for long-lived tokens.

### Can I use the refresh token from JavaScript?

No, by design. The refresh token is HTTP-only and not accessible from JavaScript. This prevents XSS attacks.

### How do I implement "Remember Me" functionality?

Increase the refresh token TTL when the user checks "Remember Me":
```php
// In your custom controller
if ($request->input('remember')) {
    config(['ra-jwt-auth.refresh_cookie.minutes' => 43200]); // 30 days
}
```

### What happens if my access token expires?

The frontend should:
1. Catch 401 errors
2. Call the `/auth/refresh` endpoint
3. Get a new access token
4. Retry the original request

See [EXAMPLES.md](./EXAMPLES.md) for implementation details.

---

## Password Reset

### Why use codes instead of email links?

8-character codes are:
- Easier to read and type (especially on mobile)
- Can be used across devices
- Less prone to email link formatting issues
- Configurable expiration and rate limiting

### How long are password reset codes valid?

By default, 60 minutes. Configure in `config/auth.php`:
```php
'passwords' => [
    'users' => [
        'expire' => 60, // minutes
    ],
],
```

### Can I customize the reset code format?

Currently, codes are 8 characters using A-Z (excluding I, O) and 2-9. You can extend `PasswordResetCodeService` to customize.

### Why are I, O, and 0 excluded from reset codes?

These characters can be easily confused (I/1, O/0), reducing user errors when manually entering codes.

### How many times can I try a reset code?

5 attempts by default. After that, the user must request a new code.

---

## Security

### What rate limits are applied?

- **Login**: 5 attempts per minute
- **Register**: 3 attempts per minute
- **Forgot Password**: 3 attempts per minute
- **Reset Password**: 3 attempts per minute
- **Token Refresh**: 10 attempts per minute

### Can I customize rate limits?

Yes, edit `routes/api.php` after publishing or extend the routes:
```php
Route::middleware('throttle:10,1')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
});
```

### What security events are logged?

- User logged in
- User login failed
- User registered
- User logged out
- Password reset requested
- Password reset completed

All events include IP address, user agent, and timestamp.

### Where are security events logged?

By default, to Laravel's log (usually `storage/logs/laravel.log`). You can create custom listeners to send logs elsewhere (e.g., database, external service).

### How do I add IP-based lockouts?

You can extend the `UserLoginFailed` listener to track failed attempts by IP and implement temporary bans.

### Is this package OWASP compliant?

The package follows OWASP best practices including:
- Password hashing (bcrypt)
- Rate limiting
- HTTP-only secure cookies
- Password strength validation
- Audit logging
- Protection against brute force attacks

---

## Customization

### Can I use a different UserResource?

Yes, configure it:
```php
'classes' => [
    'user_resource' => App\Http\Resources\CustomUserResource::class,
    // ...
]
```

### How do I add custom fields to registration?

1. Extend `RegisterRequest`:
```php
namespace App\Http\Requests;

use RaDevs\JwtAuth\Http\Requests\Auth\RegisterRequest as BaseRequest;

class CustomRegisterRequest extends BaseRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'custom_field' => 'required|string',
        ]);
    }
}
```

2. Use dependency injection in your route to use the custom request.

### Can I add roles and permissions?

Yes! Extend the `UserResource` to include roles:
```php
public function toArray($request): array
{
    return array_merge(parent::toArray($request), [
        'roles' => $this->roles->pluck('name'),
    ]);
}
```

### How do I send custom notifications?

Configure a custom notification class:
```php
'classes' => [
    'notification' => App\Notifications\CustomResetNotification::class,
]
```

---

## Testing

### How do I run the tests?

```bash
composer test
```

### How do I generate code coverage reports?

```bash
composer test-coverage
```

Reports will be in `coverage/` directory.

### Can I mock authentication in my tests?

Yes, use Laravel's `actingAs()`:
```php
$user = User::factory()->create();
$this->actingAs($user, 'api')
    ->getJson('/api/auth/me')
    ->assertStatus(200);
```

---

## Troubleshooting

### "Token could not be parsed from the request" error

Make sure you're sending the token in the Authorization header:
```
Authorization: Bearer {your-access-token}
```

### "Unauthenticated" error

1. Check that the token is valid and not expired
2. Ensure the auth guard is set to 'api' in `config/auth.php`
3. Verify the JWT secret is set (`JWT_SECRET` in `.env`)

### Refresh token not being sent

Ensure your API requests include:
```javascript
withCredentials: true // For axios
credentials: 'include' // For fetch
```

### "Too Many Requests" error

You've hit the rate limit. Wait for the timeout period (usually 1 minute) or adjust rate limits.

### CORS issues with cookies

Make sure your CORS configuration allows credentials:
```php
// config/cors.php
'supports_credentials' => true,
'allowed_origins' => ['https://your-frontend.com'],
```

### Tests failing with "Class not found"

Run:
```bash
composer dump-autoload
```

---

## Performance

### Should I cache anything?

Yes, consider caching:
- User data after authentication
- Configuration values
- JWT validation results (be careful with TTL)

### Can I use Redis for refresh tokens?

Currently, refresh tokens are stored in the users table. You can extend `AuthRepository` to use Redis instead for better performance at scale.

### How do I monitor performance?

Check the security logs for:
- Failed login attempts (potential attacks)
- High password reset volumes
- Rate limit hits

---

## Deployment

### What environment variables do I need?

```env
JWT_SECRET=your-jwt-secret-here
JWT_TTL=60
JWT_REFRESH_TTL=20160
```

### Do I need to clear cache after deployment?

Yes:
```bash
php artisan config:cache
php artisan route:cache
```

### Should I run migrations?

Yes:
```bash
php artisan migrate
```

### How do I handle zero-downtime deployments?

1. Run migrations before deploying new code
2. Use JWT's built-in token versioning
3. Implement graceful token expiration

---

## Advanced Topics

### Can I implement 2FA?

Not built-in, but you can:
1. Add a `two_factor_enabled` column to users
2. Create middleware to check 2FA status
3. Add 2FA verification endpoints

### How do I implement social authentication?

Use Laravel Socialite alongside this package:
1. Handle OAuth callback
2. Create or find user
3. Use `auth('api')->login($user)` to generate JWT

### Can I use multiple auth guards?

Yes, Laravel supports multiple guards. This package uses the 'api' guard by default.

### How do I implement device management?

You can:
1. Create a `user_devices` table
2. Store refresh tokens per device
3. Add endpoints to list/revoke devices

---

Still have questions? [Open an issue](https://github.com/ra-devs/jwt-auth/issues) on GitHub.
