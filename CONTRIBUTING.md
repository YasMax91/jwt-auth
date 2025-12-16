# Contributing to ra-devs/jwt-auth

First off, thank you for considering contributing to `ra-devs/jwt-auth`! It's people like you that make this package better for everyone.

## Table of Contents

- [Code of Conduct](#code-of-conduct)
- [How Can I Contribute?](#how-can-i-contribute)
- [Development Setup](#development-setup)
- [Coding Standards](#coding-standards)
- [Testing Requirements](#testing-requirements)
- [Pull Request Process](#pull-request-process)
- [Commit Message Guidelines](#commit-message-guidelines)

## Code of Conduct

This project adheres to a code of conduct. By participating, you are expected to uphold this code. Please be respectful, inclusive, and considerate in all interactions.

## How Can I Contribute?

### Reporting Bugs

Before creating bug reports, please check existing issues to avoid duplicates. When creating a bug report, include:

- **Clear title and description**
- **Steps to reproduce** the issue
- **Expected behavior** vs **actual behavior**
- **Environment details** (PHP version, Laravel version, package version)
- **Code samples** or **error messages** if applicable

### Suggesting Enhancements

Enhancement suggestions are tracked as GitHub issues. When creating an enhancement suggestion:

- Use a clear and descriptive title
- Provide a detailed description of the proposed functionality
- Explain why this enhancement would be useful
- Include code examples if applicable

### Pull Requests

We actively welcome your pull requests:

1. Fork the repo and create your branch from `main`
2. If you've added code that should be tested, add tests
3. Ensure the test suite passes
4. Make sure your code follows our coding standards
5. Write a clear commit message
6. Submit the pull request

## Development Setup

### 1. Clone the repository

```bash
git clone https://github.com/ra-devs/jwt-auth.git
cd jwt-auth
```

### 2. Install dependencies

```bash
composer install
```

### 3. Set up testing environment

The package uses Orchestra Testbench for testing. No additional setup is required.

### 4. Run tests

```bash
composer test
```

### 5. Check code coverage

```bash
composer test-coverage
```

## Coding Standards

This project follows PSR-12 coding standards and Laravel best practices.

### General Rules

- Use PHP 8.1+ features appropriately
- Follow PSR-4 autoloading standards
- Use type hints for parameters and return types
- Write self-documenting code with clear variable/method names
- Add PHPDoc blocks for complex methods

### Code Style

```php
<?php

namespace RaDevs\JwtAuth\Example;

use Illuminate\Http\JsonResponse;
use RaDevs\JwtAuth\Exceptions\CustomException;

class ExampleClass
{
    public function __construct(
        private readonly DependencyClass $dependency,
    ) {}

    public function exampleMethod(string $param): JsonResponse
    {
        // Clear, concise logic
        $result = $this->dependency->process($param);

        return response()->json(['result' => $result]);
    }

    /**
     * Complex method requiring documentation
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     * @throws CustomException
     */
    private function complexMethod(array $data): array
    {
        // Implementation
    }
}
```

### Laravel Conventions

- Use dependency injection via constructor
- Follow repository pattern for data access
- Use form requests for validation
- Dispatch events for important actions
- Use exceptions for error handling

### File Organization

```
src/
├── Events/              # Event classes
├── Exceptions/          # Custom exceptions
├── Http/
│   ├── Controllers/     # Controllers
│   ├── Requests/        # Form requests
│   └── Resources/       # API resources
├── Listeners/           # Event listeners
├── Models/              # Eloquent models
├── Providers/           # Service providers
├── Repositories/        # Repository classes
│   └── Contracts/       # Repository interfaces
└── Services/            # Business logic services
```

## Testing Requirements

All contributions must include appropriate tests:

### Test Types

1. **Feature Tests** - Test API endpoints end-to-end
2. **Unit Tests** - Test individual classes/methods in isolation
3. **Integration Tests** - Test interactions between components

### Writing Tests

```php
<?php

namespace RaDevs\JwtAuth\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use RaDevs\JwtAuth\Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_example_functionality(): void
    {
        // Arrange
        $user = $this->createUser();

        // Act
        $response = $this->actingAs($user, 'api')
            ->getJson('/api/auth/me');

        // Assert
        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => ['user']]);
    }
}
```

### Test Coverage Requirements

- All new features must have test coverage
- Bug fixes should include regression tests
- Aim for >80% code coverage
- Test both success and failure scenarios
- Test edge cases and boundary conditions

### Running Tests

```bash
# Run all tests
composer test

# Run specific test file
vendor/bin/phpunit tests/Feature/AuthControllerTest.php

# Run with coverage
composer test-coverage

# Run specific test method
vendor/bin/phpunit --filter test_user_can_login
```

## Pull Request Process

### Before Submitting

1. **Update tests** - Ensure all tests pass
2. **Update documentation** - Update README.md, docblocks, etc.
3. **Check code style** - Follow PSR-12 standards
4. **Update CHANGELOG.md** - Add entry under `[Unreleased]`
5. **Rebase on main** - Ensure your branch is up to date

### PR Checklist

- [ ] Tests added/updated and passing
- [ ] Documentation updated
- [ ] CHANGELOG.md updated
- [ ] Code follows style guidelines
- [ ] Commit messages are clear
- [ ] No merge conflicts
- [ ] Branch is up to date with main

### PR Template

```markdown
## Description
Brief description of changes

## Type of Change
- [ ] Bug fix (non-breaking change which fixes an issue)
- [ ] New feature (non-breaking change which adds functionality)
- [ ] Breaking change (fix or feature that would cause existing functionality to not work as expected)
- [ ] Documentation update

## How Has This Been Tested?
Describe the tests you ran

## Checklist
- [ ] My code follows the style guidelines
- [ ] I have performed a self-review
- [ ] I have commented my code where necessary
- [ ] I have updated the documentation
- [ ] My changes generate no new warnings
- [ ] I have added tests that prove my fix is effective or that my feature works
- [ ] New and existing unit tests pass locally
- [ ] I have updated CHANGELOG.md
```

### Review Process

1. Maintainer reviews the PR
2. Automated tests run via CI/CD
3. Feedback is provided (if needed)
4. PR is approved and merged, or changes are requested

## Commit Message Guidelines

We follow [Conventional Commits](https://www.conventionalcommits.org/) specification.

### Format

```
<type>(<scope>): <subject>

<body>

<footer>
```

### Types

- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code style changes (formatting, etc.)
- `refactor`: Code refactoring
- `test`: Adding or updating tests
- `chore`: Maintenance tasks

### Examples

```bash
# Feature
feat(auth): add 2FA support

# Bug fix
fix(password-reset): return missing in reset method

# Documentation
docs(readme): update installation instructions

# Breaking change
feat(auth)!: change refresh token response structure

BREAKING CHANGE: The refresh endpoint now returns token data in a different format
```

### Best Practices

- Use present tense ("add feature" not "added feature")
- Use imperative mood ("move cursor to..." not "moves cursor to...")
- Keep subject line under 72 characters
- Reference issues/PRs in footer (e.g., "Fixes #123")

## Additional Guidelines

### Security

- Never commit sensitive data (secrets, tokens, credentials)
- Follow OWASP security best practices
- Report security vulnerabilities privately to maintainers

### Performance

- Consider performance implications of changes
- Add database indexes where appropriate
- Use eager loading to prevent N+1 queries
- Cache expensive operations when possible

### Documentation

- Update README.md for user-facing changes
- Update inline comments for complex logic
- Add/update examples in docs/ directory
- Update OpenAPI spec for API changes

## Questions?

Feel free to:
- Open an issue for questions
- Reach out to maintainers
- Join discussions in existing issues/PRs

Thank you for contributing to `ra-devs/jwt-auth`! 🎉
