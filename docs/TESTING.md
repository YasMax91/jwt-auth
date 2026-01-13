# Testing Guide

## Current Status

The package includes a comprehensive test suite covering:
- Feature tests for all API endpoints
- Unit tests for services
- Security event tests
- Rate limiting tests

**✅ All tests are passing!** (21/21 tests, 57 assertions)

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

## Test Results: 21/21 Passing ✅

### ✅ All Tests Passing

**Feature Tests - AuthController (8/8)**
- ✅ User can register
- ✅ User can login with valid credentials
- ✅ User cannot login with invalid credentials
- ✅ User cannot login with nonexistent email
- ✅ Authenticated user can get profile
- ✅ Authenticated user can logout
- ✅ Login endpoint has rate limiting
- ✅ Register endpoint has rate limiting

**Feature Tests - Password Reset (6/6)**
- ✅ User can request password reset
- ✅ Password reset request has rate limiting
- ✅ User can reset password with valid code
- ✅ User cannot reset password with invalid code
- ✅ User cannot reset password with expired code
- ✅ Password reset code lockout after max attempts

**Unit Tests - PasswordResetCodeService (7/7)**
- ✅ Issue code creates reset code
- ✅ Issue code throws exception when rate limited
- ✅ Verify code returns true for valid code
- ✅ Verify code returns false for invalid code
- ✅ Verify code increments attempts on wrong code
- ✅ Reset password updates user password
- ✅ Reset password marks code as used

## Contributing

If you'd like to help improve the test suite:

1. Set up the development environment
2. Run tests to verify everything works
3. Add new test cases for new features
4. Submit a pull request

See [CONTRIBUTING.md](../CONTRIBUTING.md) for more details.

## Test Coverage

**Current status:** ✅ All 21 tests passing (100%)
**Test coverage:** Run `composer test-coverage` to see detailed coverage report

### Test Environment Setup

The test environment is properly configured with:
- ✅ Exception handler registered in `TestCase`
- ✅ Custom `TestExceptionHandler` for proper exception rendering
- ✅ Events and notifications properly faked
- ✅ Routes loaded correctly
- ✅ Database migrations set up for testing

### Future Improvements

- [ ] Add integration tests for different configurations
- [ ] Add performance/load tests
- [ ] Add mutation testing
- [ ] Increase code coverage to 80%+
