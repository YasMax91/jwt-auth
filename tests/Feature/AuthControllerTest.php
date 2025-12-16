<?php

namespace RaDevs\JwtAuth\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Event;
use RaDevs\JwtAuth\Tests\TestCase;
use RaDevs\JwtAuth\Events\UserLoggedIn;
use RaDevs\JwtAuth\Events\UserLoginFailed;
use RaDevs\JwtAuth\Events\UserRegistered;
use RaDevs\JwtAuth\Events\UserLoggedOut;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake();
    }

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe.test@gmail.com',
            'phone' => '+1234567890',
            'password' => 'T3st!ngP@ssw0rdXq9Z',
            'password_confirmation' => 'T3st!ngP@ssw0rdXq9Z',
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'success',
            'status',
            'message',
            'data' => [
                'user' => ['id', 'name', 'email'],
            ],
        ]);

        Event::assertDispatched(UserRegistered::class);
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = $this->createUser();

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'status',
            'message',
            'data' => [
                'user',
                'token' => [
                    'access_token',
                    'token_type',
                    'expires_in',
                ],
            ],
        ]);

        Event::assertDispatched(UserLoggedIn::class);
    }

    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        $user = $this->createUser();

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
        Event::assertDispatched(UserLoginFailed::class);
    }

    public function test_user_cannot_login_with_nonexistent_email(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(404);
        Event::assertDispatched(UserLoginFailed::class);
    }

    public function test_authenticated_user_can_get_profile(): void
    {
        $user = $this->createUser();
        $token = auth('api')->login($user);

        $response = $this->getJson('/api/auth/me', [
            'Authorization' => "Bearer $token",
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'status',
            'message',
            'data' => [
                'user' => ['id', 'name', 'email'],
            ],
        ]);
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = $this->createUser();
        $token = auth('api')->login($user);

        $response = $this->postJson('/api/auth/logout', [], [
            'Authorization' => "Bearer $token",
        ]);

        $response->assertStatus(200);
        Event::assertDispatched(UserLoggedOut::class);
    }

    public function test_login_endpoint_has_rate_limiting(): void
    {
        $user = $this->createUser();

        // Make 6 requests (exceeding the limit of 5)
        for ($i = 0; $i < 6; $i++) {
            $response = $this->postJson('/api/auth/login', [
                'email' => 'test@example.com',
                'password' => 'wrongpassword',
            ]);
        }

        // The 6th request should be rate limited
        $response->assertStatus(429);
    }

    public function test_register_endpoint_has_rate_limiting(): void
    {
        // Make 4 requests (exceeding the limit of 3)
        for ($i = 0; $i < 4; $i++) {
            $response = $this->postJson('/api/auth/register', [
                'name' => "User$i",
                'last_name' => 'Test',
                'email' => "user$i.test@gmail.com",
                'phone' => "+123456789$i",
                'password' => 'T3st!ngP@ssw0rdXq9Z',
                'password_confirmation' => 'T3st!ngP@ssw0rdXq9Z',
            ]);
        }

        // The 4th request should be rate limited
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
