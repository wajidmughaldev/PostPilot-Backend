<?php

namespace Tests\Feature\Profile;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DeleteAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_delete_account_requires_authentication(): void
    {
        $this->deleteJson('/api/profile')
            ->assertUnauthorized()
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized.',
            ]);
    }

    public function test_delete_account_requires_correct_current_password(): void
    {
        $user = User::factory()->create([
            'password' => 'Secret123',
        ]);

        $this->actingAs($user, 'web')
            ->deleteJson('/api/profile', [
                'current_password' => 'Wrong123',
            ])
            ->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Current password is incorrect.',
            ])
            ->assertJsonValidationErrors(['current_password']);
    }

    public function test_delete_account_deletes_user_and_logs_out(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'password' => 'Secret123',
            'avatar_path' => 'avatars/test.png',
        ]);

        Storage::disk('public')->put('avatars/test.png', 'avatar');

        $response = $this->actingAs($user, 'web')->deleteJson('/api/profile', [
            'current_password' => 'Secret123',
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Account deleted successfully.',
                'data' => [],
            ]);

        $this->assertGuest('web');
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        Storage::disk('public')->assertMissing('avatars/test.png');
    }
}
