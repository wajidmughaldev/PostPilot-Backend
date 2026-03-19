<?php

namespace Tests\Feature\Profile;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_requires_authentication(): void
    {
        $this->getJson('/api/profile')
            ->assertUnauthorized()
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized.',
            ]);
    }

    public function test_profile_returns_authenticated_user(): void
    {
        $user = User::factory()->create([
            'username' => 'alexjohnson',
        ]);

        $this->actingAs($user, 'web')
            ->getJson('/api/profile')
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Profile retrieved successfully.',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'username' => 'alexjohnson',
                    ],
                ],
            ]);
    }

    public function test_profile_update_normalizes_and_saves_user_fields(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'web')
            ->patchJson('/api/profile', [
                'name' => '  Wajid   Khan  ',
                'email' => '  WAJID@example.com ',
                'username' => ' @Wajid_Khan ',
            ])
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Profile updated successfully.',
                'data' => [
                    'user' => [
                        'name' => 'Wajid Khan',
                        'email' => 'wajid@example.com',
                        'username' => 'wajid_khan',
                    ],
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Wajid Khan',
            'email' => 'wajid@example.com',
            'username' => 'wajid_khan',
        ]);
    }

    public function test_profile_update_rejects_duplicate_email_and_username(): void
    {
        User::factory()->create([
            'email' => 'taken@example.com',
            'username' => 'taken_user',
        ]);

        $user = User::factory()->create();

        $this->actingAs($user, 'web')
            ->patchJson('/api/profile', [
                'name' => 'User Name',
                'email' => 'taken@example.com',
                'username' => 'taken_user',
            ])
            ->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'An account with this email already exists.',
            ])
            ->assertJsonValidationErrors(['email', 'username']);
    }

    public function test_password_update_requires_correct_current_password(): void
    {
        $user = User::factory()->create([
            'password' => 'OldPass123',
        ]);

        $this->actingAs($user, 'web')
            ->patchJson('/api/profile/password', [
                'current_password' => 'WrongPass123',
                'password' => 'NewPass123',
                'password_confirmation' => 'NewPass123',
            ])
            ->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Current password is incorrect.',
            ])
            ->assertJsonValidationErrors(['current_password']);
    }

    public function test_password_update_changes_password(): void
    {
        $user = User::factory()->create([
            'password' => 'OldPass123',
        ]);

        $this->actingAs($user, 'web')
            ->patchJson('/api/profile/password', [
                'current_password' => 'OldPass123',
                'password' => 'NewPass123',
                'password_confirmation' => 'NewPass123',
            ])
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Password updated successfully.',
                'data' => [],
            ]);

        $this->assertTrue(Hash::check('NewPass123', $user->fresh()->password));
    }
}
