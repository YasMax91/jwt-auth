# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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

[Unreleased]: https://github.com/ra-devs/jwt-auth/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/ra-devs/jwt-auth/releases/tag/v1.0.0
