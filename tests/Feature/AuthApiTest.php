<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_hello_endpoint_returns_welcome_message(): void
    {
        $this->getJson('/api/hello')
            ->assertOk()
            ->assertExactJson(['message' => 'Welcome to the Laravel 13 API']);
    }

    public function test_registration_returns_public_user_fields_and_hashes_password(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertCreated()
            ->assertJsonPath('message', 'Registration successful')
            ->assertJsonPath('user.name', 'Test User')
            ->assertJsonMissingPath('user.password')
            ->assertJsonMissingPath('access_token');

        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
        $this->assertTrue(Hash::check('password123', User::firstOrFail()->password));
    }

    public function test_registration_requires_confirmed_password_and_unique_email(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different123',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_login_returns_a_token_for_valid_credentials(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ])->assertOk()
            ->assertJsonPath('message', 'Login successful')
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonStructure(['access_token'])
            ->assertJsonMissingPath('user');
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['email'])
            ->assertJsonPath('errors.email.0', 'Invalid credentials.');
    }

    public function test_profile_requires_a_valid_token_and_returns_only_public_fields(): void
    {
        $this->getJson('/api/profile')->assertUnauthorized();

        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $this->withToken($token)->getJson('/api/profile')
            ->assertOk()
            ->assertExactJson([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ]);
    }

    public function test_unauthenticated_api_requests_without_json_headers_return_json(): void
    {
        $this->get('/api/profile')
            ->assertUnauthorized()
            ->assertExactJson(['message' => 'Unauthenticated.']);
    }

    public function test_logout_revokes_the_current_token(): void
    {
        $user = User::factory()->create();
        $newToken = $user->createToken('test-token');
        $tokenId = $newToken->accessToken->id;

        $this->withToken($newToken->plainTextToken)->postJson('/api/logout')
            ->assertOk()
            ->assertExactJson(['message' => 'Logged out successfully']);

        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $tokenId]);

        $this->app['auth']->forgetGuards();
        $this->withToken($newToken->plainTextToken)->getJson('/api/profile')->assertUnauthorized();
    }
}
