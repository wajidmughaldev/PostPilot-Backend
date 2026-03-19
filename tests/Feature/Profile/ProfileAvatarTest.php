<?php

namespace Tests\Feature\Profile;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileAvatarTest extends TestCase
{
    use RefreshDatabase;

    public function test_avatar_upload_requires_authentication(): void
    {
        $this->postJson('/api/profile/avatar')
            ->assertUnauthorized()
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized.',
            ]);
    }

    public function test_avatar_upload_stores_file_and_returns_user_payload(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aOe0AAAAASUVORK5CYII=');

        $response = $this->actingAs($user, 'web')->postJson('/api/profile/avatar', [
            'avatar' => UploadedFile::fake()->createWithContent('avatar.png', $png),
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Profile photo updated successfully.',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                    ],
                ],
            ]);

        $this->assertNotNull($user->fresh()->avatar_path);
        Storage::disk('public')->assertExists($user->fresh()->avatar_path);
    }
}
