<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AuthEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_creates_user_and_returns_human_friendly_payload(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'Secure123',
            'password_confirmation' => 'Secure123',
        ]);

        $response
            ->assertCreated()
            ->assertJson([
                'success' => true,
                'message' => 'Account created successfully. You can now sign in.',
                'data' => [
                    'user' => [
                        'name' => 'Jane Doe',
                        'email' => 'jane@example.com',
                    ],
                ],
            ]);

        $this->assertGuest('web');
        $this->assertDatabaseHas('users', ['email' => 'jane@example.com']);
    }

    public function test_register_returns_human_friendly_duplicate_email_error(): void
    {
        User::factory()->create([
            'email' => 'jane@example.com',
        ]);

        $response = $this->postJson('/api/register', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'Secure123',
            'password_confirmation' => 'Secure123',
        ]);

        $response
            ->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'An account with this email already exists.',
            ])
            ->assertJsonValidationErrors(['email']);
    }

    public function test_register_normalizes_name_spacing_and_email_case(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => '  Jane    Doe  ',
            'email' => '  JANE@EXAMPLE.COM  ',
            'password' => 'Secure123',
            'password_confirmation' => 'Secure123',
        ]);

        $response
            ->assertCreated()
            ->assertJson([
                'success' => true,
                'data' => [
                    'user' => [
                        'name' => 'Jane Doe',
                        'email' => 'jane@example.com',
                    ],
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);
    }

    public function test_login_returns_authenticated_payload_for_valid_credentials(): void
    {
        $user = User::factory()->create([
            'password' => 'Secure123',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'Secure123',
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Login completed successfully.',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'email' => $user->email,
                    ],
                    'onboarding' => [
                        'organization_required' => true,
                        'organization_id' => null,
                    ],
                ],
            ]);

        $this->assertAuthenticated('web');
    }

    public function test_login_returns_validation_error_for_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'jane@example.com',
            'password' => 'Secure123',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'jane@example.com',
            'password' => 'Wrong123',
        ]);

        $response
            ->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'The provided credentials are incorrect.',
            ])
            ->assertJsonValidationErrors(['email']);

        $this->assertGuest('web');
    }

    public function test_logout_requires_authentication(): void
    {
        $this->postJson('/api/auth/logout')
            ->assertUnauthorized()
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized.',
            ]);
    }

    public function test_logout_clears_authenticated_session(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'web')->postJson('/api/auth/logout');

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Logout completed successfully.',
                'data' => [],
            ]);

        $this->assertGuest('web');
    }

    public function test_me_returns_authenticated_user_payload(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'web')->getJson('/api/auth/me');

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Authenticated user retrieved successfully.',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'email' => $user->email,
                    ],
                    'onboarding' => [
                        'organization_required' => true,
                        'organization_id' => null,
                    ],
                ],
            ]);
    }

    public function test_me_requires_authentication(): void
    {
        $this->getJson('/api/auth/me')
            ->assertUnauthorized()
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized.',
            ]);
    }

    public function test_forgot_password_returns_success_and_dispatches_notification_for_known_email(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'jane@example.com',
        ]);

        $response = $this->postJson('/api/auth/forgot-password', [
            'email' => 'jane@example.com',
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'If the account exists, a password reset link has been sent to the email address provided.',
                'data' => [],
            ]);

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_forgot_password_returns_success_for_unknown_email_without_notification(): void
    {
        Notification::fake();

        $response = $this->postJson('/api/auth/forgot-password', [
            'email' => 'missing@example.com',
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'If the account exists, a password reset link has been sent to the email address provided.',
                'data' => [],
            ]);

        Notification::assertNothingSent();
    }

    public function test_reset_password_updates_password_and_allows_login_with_new_password(): void
    {
        $user = User::factory()->create([
            'email' => 'jane@example.com',
            'password' => 'OldPass123',
        ]);

        $token = Password::broker()->createToken($user);

        $response = $this->postJson('/api/auth/reset-password', [
            'token' => $token,
            'email' => 'jane@example.com',
            'password' => 'NewPass123',
            'password_confirmation' => 'NewPass123',
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Password reset completed successfully.',
                'data' => [],
            ]);

        $this->assertTrue(Hash::check('NewPass123', $user->fresh()->password));

        $this->postJson('/api/auth/login', [
            'email' => 'jane@example.com',
            'password' => 'NewPass123',
        ])->assertOk();
    }
}
