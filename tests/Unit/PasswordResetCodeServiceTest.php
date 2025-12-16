<?php

namespace RaDevs\JwtAuth\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use RaDevs\JwtAuth\Tests\TestCase;
use RaDevs\JwtAuth\Services\PasswordResetCodeService;
use RaDevs\JwtAuth\Models\PasswordResetCode;
use RaDevs\JwtAuth\Exceptions\PasswordResetException;

class PasswordResetCodeServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PasswordResetCodeService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PasswordResetCodeService();
    }

    public function test_issue_code_creates_reset_code(): void
    {
        $email = 'test@example.com';
        $this->createUser($email);

        $this->service->issueCode(
            email: $email,
            ipAddress: '127.0.0.1',
            userAgent: 'Test Agent',
            ttlMinutes: 60
        );

        $this->assertDatabaseHas('password_reset_codes', [
            'email' => $email,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Agent',
        ]);
    }

    public function test_issue_code_throws_exception_when_rate_limited(): void
    {
        $email = 'test@example.com';
        $this->createUser($email);

        $this->service->issueCode(
            email: $email,
            ipAddress: '127.0.0.1',
            userAgent: 'Test Agent',
            ttlMinutes: 60
        );

        $this->expectException(PasswordResetException::class);

        // Immediate second call should fail
        $this->service->issueCode(
            email: $email,
            ipAddress: '127.0.0.1',
            userAgent: 'Test Agent',
            ttlMinutes: 60
        );
    }

    public function test_verify_code_returns_true_for_valid_code(): void
    {
        $email = 'test@example.com';
        $code = 'ABCD1234';

        PasswordResetCode::create([
            'email' => $email,
            'code_hash' => Hash::make($code),
            'attempts' => 0,
            'max_attempts' => 5,
            'expires_at' => now()->addHour(),
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Agent',
        ]);

        $result = $this->service->verifyCode($email, $code);

        $this->assertTrue($result);
    }

    public function test_verify_code_returns_false_for_invalid_code(): void
    {
        $email = 'test@example.com';
        $code = 'ABCD1234';

        PasswordResetCode::create([
            'email' => $email,
            'code_hash' => Hash::make($code),
            'attempts' => 0,
            'max_attempts' => 5,
            'expires_at' => now()->addHour(),
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Agent',
        ]);

        $result = $this->service->verifyCode($email, 'WRONGCODE');

        $this->assertFalse($result);
    }

    public function test_verify_code_increments_attempts_on_wrong_code(): void
    {
        $email = 'test@example.com';
        $code = 'ABCD1234';

        $record = PasswordResetCode::create([
            'email' => $email,
            'code_hash' => Hash::make($code),
            'attempts' => 0,
            'max_attempts' => 5,
            'expires_at' => now()->addHour(),
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Agent',
        ]);

        $this->service->verifyCode($email, 'WRONGCODE');

        $record->refresh();
        $this->assertEquals(1, $record->attempts);
    }

    public function test_reset_password_updates_user_password(): void
    {
        $email = 'test@example.com';
        $code = 'ABCD1234';
        $user = $this->createUser($email);

        PasswordResetCode::create([
            'email' => $email,
            'code_hash' => Hash::make($code),
            'attempts' => 0,
            'max_attempts' => 5,
            'expires_at' => now()->addHour(),
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Agent',
        ]);

        $this->service->resetPassword(
            email: $email,
            code: $code,
            newPassword: 'NewPassword123!'
        );

        $user->refresh();
        $this->assertTrue(Hash::check('NewPassword123!', $user->password));
    }

    public function test_reset_password_marks_code_as_used(): void
    {
        $email = 'test@example.com';
        $code = 'ABCD1234';
        $user = $this->createUser($email);

        $record = PasswordResetCode::create([
            'email' => $email,
            'code_hash' => Hash::make($code),
            'attempts' => 0,
            'max_attempts' => 5,
            'expires_at' => now()->addHour(),
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Agent',
        ]);

        $this->service->resetPassword(
            email: $email,
            code: $code,
            newPassword: 'NewPassword123!'
        );

        $record->refresh();
        $this->assertNotNull($record->used_at);
    }

    protected function createUser(string $email = 'test@example.com')
    {
        $userModel = config('ra-jwt-auth.classes.user_model');

        return $userModel::create([
            'name' => 'Test',
            'email' => $email,
            'password' => 'password',
        ]);
    }
}
