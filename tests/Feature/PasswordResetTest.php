<?php

namespace RaDevs\JwtAuth\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use RaDevs\JwtAuth\Tests\TestCase;
use RaDevs\JwtAuth\Events\PasswordResetRequested;
use RaDevs\JwtAuth\Events\PasswordResetCompleted;
use RaDevs\JwtAuth\Models\PasswordResetCode;
use RaDevs\JwtAuth\Notifications\ApiResetPasswordCodeNotification;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake();
        Notification::fake();
    }

    public function test_user_can_request_password_reset(): void
    {
        $user = $this->createUser();

        $response = $this->postJson('/api/auth/forgot-password', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('password_reset_codes', [
            'email' => 'test@example.com',
        ]);

        Event::assertDispatched(PasswordResetRequested::class);
        Notification::assertSentTo($user, ApiResetPasswordCodeNotification::class);
    }

    public function test_password_reset_request_has_rate_limiting(): void
    {
        $user = $this->createUser();

        // First request
        $this->postJson('/api/auth/forgot-password', [
            'email' => 'test@example.com',
        ]);

        // Immediate second request should fail
        $response = $this->postJson('/api/auth/forgot-password', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(429);
    }

    public function test_user_can_reset_password_with_valid_code(): void
    {
        $user = $this->createUser();
        $code = 'ABCD2345';

        PasswordResetCode::create([
            'email' => 'test@example.com',
            'code_hash' => Hash::make($code),
            'attempts' => 0,
            'max_attempts' => 5,
            'expires_at' => now()->addHour(),
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Agent',
        ]);

        $response = $this->postJson('/api/auth/reset-password', [
            'email' => 'test@example.com',
            'code' => $code,
            'password' => 'N3wP@ssw0rdTst7Xq',
            'password_confirmation' => 'N3wP@ssw0rdTst7Xq',
        ]);

        $response->assertStatus(200);
        Event::assertDispatched(PasswordResetCompleted::class);

        // Verify password was changed
        $user->refresh();
        $this->assertTrue(Hash::check('N3wP@ssw0rdTst7Xq', $user->password));
    }

    public function test_user_cannot_reset_password_with_invalid_code(): void
    {
        $user = $this->createUser();
        $code = 'ABCD2345';

        PasswordResetCode::create([
            'email' => 'test@example.com',
            'code_hash' => Hash::make($code),
            'attempts' => 0,
            'max_attempts' => 5,
            'expires_at' => now()->addHour(),
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Agent',
        ]);

        $response = $this->postJson('/api/auth/reset-password', [
            'email' => 'test@example.com',
            'code' => 'WRNG2345',
            'password' => 'N3wP@ssw0rdTst7Xq',
            'password_confirmation' => 'N3wP@ssw0rdTst7Xq',
        ]);

        $response->assertStatus(400);
        Event::assertNotDispatched(PasswordResetCompleted::class);
    }

    public function test_user_cannot_reset_password_with_expired_code(): void
    {
        $user = $this->createUser();
        $code = 'ABCD2345';

        PasswordResetCode::create([
            'email' => 'test@example.com',
            'code_hash' => Hash::make($code),
            'attempts' => 0,
            'max_attempts' => 5,
            'expires_at' => now()->subHour(), // Expired
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Agent',
        ]);

        $response = $this->postJson('/api/auth/reset-password', [
            'email' => 'test@example.com',
            'code' => $code,
            'password' => 'N3wP@ssw0rdTst7Xq',
            'password_confirmation' => 'N3wP@ssw0rdTst7Xq',
        ]);

        $response->assertStatus(400);
    }

    public function test_password_reset_code_lockout_after_max_attempts(): void
    {
        $user = $this->createUser();
        $code = 'ABCD2345';

        PasswordResetCode::create([
            'email' => 'test@example.com',
            'code_hash' => Hash::make($code),
            'attempts' => 0,
            'max_attempts' => 5,
            'expires_at' => now()->addHour(),
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Agent',
        ]);

        // Make 6 attempts with wrong code
        for ($i = 0; $i < 6; $i++) {
            $this->postJson('/api/auth/reset-password', [
                'email' => 'test@example.com',
                'code' => 'WRNG2345',
                'password' => 'N3wP@ssw0rdTst7Xq',
                'password_confirmation' => 'N3wP@ssw0rdTst7Xq',
            ]);
        }

        // After max attempts, even correct code should fail
        $response = $this->postJson('/api/auth/reset-password', [
            'email' => 'test@example.com',
            'code' => $code,
            'password' => 'N3wP@ssw0rdTst7Xq',
            'password_confirmation' => 'N3wP@ssw0rdTst7Xq',
        ]);

        $response->assertStatus(429);
    }

    protected function createUser()
    {
        $userModel = config('ra-jwt-auth.classes.user_model');

        return $userModel::create([
            'name' => 'Test',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);
    }
}
