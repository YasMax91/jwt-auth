# Testing Guide

## Current Status

The package includes a comprehensive test suite covering:
- Feature tests for all API endpoints
- Unit tests for services
- Security event tests
- Rate limiting tests

**Note:** Tests are currently in development and require additional configuration. Some tests (10/21) are passing, but others need adjustments for proper integration with the test environment.

## Running Tests

```bash
# Run all tests
composer test

# Run without coverage
vendor/bin/phpunit --no-coverage

# Run specific test file
vendor/bin/phpunit tests/Feature/AuthControllerTest.php

# Run with testdox output
vendor/bin/phpunit --testdox
```

## Test Structure

```
tests/
├── TestCase.php              # Base test case with test environment setup
├── Fixtures/
│   └── User.php              # Test user model
├── database/
│   └── migrations/           # Test database migrations
├── Feature/
│   ├── AuthControllerTest.php    # API endpoint tests
│   └── PasswordResetTest.php     # Password reset flow tests
└── Unit/
    └── PasswordResetCodeServiceTest.php  # Service logic tests
```

## Test Results: 10/21 Passing ✅

### ✅ Passing Tests (10)

**Unit Tests - PasswordResetCodeService (7/7)**
- ✅ Issue code creates reset code
- ✅ Issue code throws exception when rate limited
- ✅ Verify code returns true for valid code
- ✅ Verify code returns false for invalid code
- ✅ Verify code increments attempts on wrong code
- ✅ Reset password updates user password
- ✅ Reset password marks code as used

**Feature Tests - Rate Limiting (2/8)**
- ✅ Login endpoint rate limiting
- ✅ Register endpoint rate limiting

**Feature Tests - Password Reset (1/6)**
- ✅ Password reset code lockout after max attempts

## Known Issues

### ⚠️ Failing Tests (11)

**Feature Tests - Authentication (6/8)**
- ❌ User can register
- ❌ User can login with valid credentials
- ❌ User cannot login with invalid credentials
- ❌ User cannot login with nonexistent email
- ❌ Authenticated user can get profile
- ❌ Authenticated user can logout

**Feature Tests - Password Reset (5/6)**
- ❌ User can request password reset
- ❌ Password reset request has rate limiting
- ❌ User can reset password with valid code
- ❌ User cannot reset password with invalid code
- ❌ User cannot reset password with expired code

### Root Causes

These tests fail with HTTP 500 errors due to:
1. **Exception handling** - Custom exceptions not being caught properly in test environment
2. **Handler registration** - Exception handler needs to be registered in TestCase
3. **Response formatting** - ApiJsonResponse facade behavior in tests
4. **Event dispatching** - Events may need to be faked in some tests

## Contributing

If you'd like to help improve the test suite:

1. Set up the development environment
2. Run tests to see current failures
3. Fix individual test cases
4. Submit a pull request

See [CONTRIBUTING.md](../CONTRIBUTING.md) for more details.

## Test Coverage Goals

**Target coverage:** >80%
**Current coverage:** ~48% (10/21 tests passing)

### Priority Fixes Needed

1. **Register exception handler in TestCase** - Most critical
2. **Mock ApiJsonResponse facade properly** - For test environment
3. **Configure exception handling** - Ensure custom exceptions are caught
4. **Mock events/notifications** - Already using Event::fake() but may need adjustments

### Next Steps for Contributors

To fix the failing tests:

1. **Update TestCase** to register exception handler:
```php
protected function resolveApplicationExceptionHandler($app)
{
    $app->singleton(
        \Illuminate\Contracts\Debug\ExceptionHandler::class,
        \RaDevs\JwtAuth\Exceptions\Handler::class
    );
}
```

2. **Test response debugging** - Add `->dump()` to failing tests to see actual errors
3. **Mock external dependencies** - Ensure notifications are properly faked
4. **Verify routing** - Check that routes are loaded correctly in test environment

### Low Priority

- [ ] Add integration tests for different configurations
- [ ] Add performance/load tests
- [ ] Add mutation testing
- [ ] Increase coverage to 80%+
