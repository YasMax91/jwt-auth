# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.4.0] - 2026-01-13

### Added
- Environment variable configuration for all package settings
  - Refresh cookie configuration via env variables:
    - `RA_JWT_AUTH_REFRESH_COOKIE_NAME`
    - `RA_JWT_AUTH_REFRESH_COOKIE_MINUTES`
    - `RA_JWT_AUTH_REFRESH_COOKIE_SECURE`
    - `RA_JWT_AUTH_REFRESH_COOKIE_HTTP_ONLY`
    - `RA_JWT_AUTH_REFRESH_COOKIE_SAME_SITE`
    - `RA_JWT_AUTH_REFRESH_COOKIE_PATH`
    - `RA_JWT_AUTH_REFRESH_COOKIE_DOMAIN`
  - Password reset configuration via env variables:
    - `RA_JWT_AUTH_PASSWORD_RESET_CODE_LENGTH`
    - `RA_JWT_AUTH_PASSWORD_RESET_CODE_ALPHABET`
    - `RA_JWT_AUTH_PASSWORD_RESET_MAX_ATTEMPTS`
    - `RA_JWT_AUTH_PASSWORD_RESET_RATE_LIMIT_SECONDS`
  - Rate limits configuration via env variables:
    - `RA_JWT_AUTH_RATE_LIMIT_LOGIN`
    - `RA_JWT_AUTH_RATE_LIMIT_REGISTER`
    - `RA_JWT_AUTH_RATE_LIMIT_FORGOT_PASSWORD`
    - `RA_JWT_AUTH_RATE_LIMIT_RESET_PASSWORD`
    - `RA_JWT_AUTH_RATE_LIMIT_REFRESH`
  - Validation rules configuration via env variables:
    - Password: `RA_JWT_AUTH_PASSWORD_MIN_LENGTH`, `RA_JWT_AUTH_PASSWORD_MIN_LENGTH_LOGIN`, `RA_JWT_AUTH_PASSWORD_REQUIRE_LETTERS`, `RA_JWT_AUTH_PASSWORD_REQUIRE_MIXED_CASE`, `RA_JWT_AUTH_PASSWORD_REQUIRE_NUMBERS`, `RA_JWT_AUTH_PASSWORD_REQUIRE_SYMBOLS`
    - Email: `RA_JWT_AUTH_EMAIL_MAX_LENGTH`
    - Name: `RA_JWT_AUTH_NAME_MAX_LENGTH`
    - Phone: `RA_JWT_AUTH_PHONE_PATTERN`

### Changed
- All Request classes now use configurable validation rules from config/env
- `PasswordResetCodeService` now uses configurable settings for code generation and rate limiting
- Rate limits in routes now use configurable values from config
- Password validation rules are now fully configurable (can disable requirements individually)
- Code validation in `ResetPasswordRequest` and `CanResetPasswordRequest` now uses configurable alphabet

## [1.3.2] - 2026-01-13

### Security
- Fixed information disclosure vulnerability in forgot password endpoint
  - Now throws `UserNotFoundException` when user doesn't exist instead of returning generic success message
  - Prevents email enumeration attacks

### Changed
- `AuthController::forgot()` now throws exception for non-existent users
- Improved error message clarity: "The code has been sent" only when code is actually sent

## [1.3.1] - 2026-01-13

### Added
- Configurable password reset code length
  - `password_reset.code_length` configuration option (default: 8)
  - Dynamic code validation based on configured length

### Changed
- `PasswordResetCodeService` now uses configurable code length instead of hardcoded value
- `ResetPasswordRequest` now validates code length dynamically based on configuration
- Removed `uncompromised()` password validation from registration and password reset (can cause external API calls)

## [1.3.0] - 2026-01-12

### Added
- Support for multiple login fields (email, username, phone, etc.)
  - `login.search_fields` configuration option to specify multiple fields for user search
  - Automatic `required_without_all` validation for multiple login fields
  - `LoginRequest::findUserBySearchFields()` method to find user by any configured field
  - `LoginRequest::getSearchFields()` method to get list of search fields
- Enhanced login flexibility - users can now login with email, username, phone, or any configured field

### Changed
- `LoginRequest` now supports multiple login fields with automatic validation
- `AuthController::login()` now searches user by multiple fields instead of single field
- Login validation automatically adjusts based on configured search fields

## [1.2.0] - 2026-01-12

### Added
- Configurable login fields and login field type
  - `login.field` configuration option to change login field (email/username/phone)
  - `login.fields` configuration option for customizing validation rules
  - `LoginRequest::getLoginField()` and `getLoginValue()` methods
- Configurable password reset fields
  - `password_reset.fields` configuration option for customizing validation rules
  - `password_reset.exclude_from_update` option to exclude service fields
  - `ResetPasswordRequest::getResetData()` method for preparing reset data
- Configurable forgot password fields
  - `forgot_password.fields` configuration option for customizing validation rules
- Enhanced documentation with examples for customizing login, password reset and forgot password fields

### Changed
- `LoginRequest` now uses configurable fields and supports custom login field types
- `ResetPasswordRequest` now uses configurable fields with fallback to default rules
- `ForgotRequest` now uses configurable fields with fallback to default rules
- `AuthController::login()` now uses configurable login field instead of hardcoded 'email'

## [1.1.0] - 2026-01-05

### Added
- Configurable registration fields via config file
  - `registration.fields` configuration option for customizing validation rules
  - `registration.exclude_from_create` option to exclude service fields from user creation
  - Support for `{user_table}` placeholder in validation rules
  - `RegisterRequest::getRegistrationData()` method for preparing user data
- Enhanced documentation with examples for customizing registration fields

### Changed
- `RegisterRequest` now uses configurable fields with fallback to default rules
- `AuthController::register()` now uses prepared registration data (excludes service fields)

## [Unreleased]

### Added
- Custom exception classes for better error handling
  - `JwtAuthException` base class
  - `InvalidCredentialsException` for authentication failures
  - `UserNotFoundException` for missing users
  - `InvalidTokenException` for token-related errors
  - `PasswordResetException` with static factory methods
- Security event logging system
  - `UserLoggedIn` event
  - `UserLoginFailed` event
  - `UserRegistered` event
  - `UserLoggedOut` event
  - `PasswordResetRequested` event
  - `PasswordResetCompleted` event
  - `LogSecurityEvent` listener for logging all security events
- Rate limiting on all authentication endpoints
  - Login: 5 attempts per minute
  - Register: 3 attempts per minute
  - Password reset: 3 attempts per minute
  - Token refresh: 10 attempts per minute
- Comprehensive PHPUnit test suite
  - Feature tests for all API endpoints
  - Unit tests for PasswordResetCodeService
  - Test coverage for rate limiting
  - Event assertion tests
- Database indexes for performance
  - Index on `users.email`
  - Index on `users.refresh_token`
  - Composite index on `password_reset_codes` table
- OpenAPI 3.0 documentation (`docs/openapi.yaml`)
- Expanded documentation
  - Integration examples for Vue.js and React (`docs/EXAMPLES.md`)
  - Frequently Asked Questions (`docs/FAQ.md`)
  - Contributing guidelines (`CONTRIBUTING.md`)
- Migration for adding `refresh_token` column to users table

### Changed
- Improved error responses with error codes for programmatic handling
- Updated AuthController to dispatch security events
- Refactored exception handling in AuthController and PasswordResetCodeService

### Fixed
- Critical bug in `AuthController::reset()` method - missing return statement (line 138)
- Potential race condition in password reset with database locking

### Security
- Added comprehensive audit logging for security events
- Implemented rate limiting to prevent brute force attacks
- Enhanced error messages with error codes while maintaining security

## [1.0.0] - 2025-01-01

### Added
- Initial release
- JWT authentication endpoints (login, register, logout, refresh, me)
- Password reset via 8-character alphanumeric codes
- HTTP-only secure refresh token cookies
- Configurable User model, Resource, and Repositories
- Email notifications for password reset
- Rate limiting on password reset requests (60 second cooldown)
- Brute force protection (5 max attempts per reset code)
- Publishable configuration, migrations, and views
- Repository pattern with interfaces for extensibility
- Integration with `tymon/jwt-auth` and `ra-devs/api-json-response`
- Support for Laravel 10, 11, and 12+
- PHP 8.1+ support

### Dependencies
- PHP: ^8.1
- Laravel: ^10.0|^11.0|^12.0
- tymon/jwt-auth: ^2.0
- ra-devs/api-json-response: ^1.0

[Unreleased]: https://github.com/ra-devs/jwt-auth/compare/v1.4.0...HEAD
[1.4.0]: https://github.com/ra-devs/jwt-auth/compare/v1.3.2...v1.4.0
[1.3.2]: https://github.com/ra-devs/jwt-auth/compare/v1.3.1...v1.3.2
[1.3.1]: https://github.com/ra-devs/jwt-auth/compare/v1.3.0...v1.3.1
[1.3.0]: https://github.com/ra-devs/jwt-auth/compare/v1.2.0...v1.3.0
[1.2.0]: https://github.com/ra-devs/jwt-auth/compare/v1.1.0...v1.2.0
[1.1.0]: https://github.com/ra-devs/jwt-auth/compare/v1.0.1...v1.1.0
[1.0.0]: https://github.com/ra-devs/jwt-auth/releases/tag/v1.0.0
