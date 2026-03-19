<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_require_authentication(): void
    {
        $this->getJson('/api/settings')
            ->assertUnauthorized()
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized.',
            ]);
    }

    public function test_settings_show_endpoint_creates_and_returns_defaults(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'web')
            ->getJson('/api/settings')
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Settings retrieved successfully.',
                'data' => [
                    'settings' => [
                        'theme_preference' => 'system',
                        'interface_language' => 'en-US',
                        'timezone' => 'UTC',
                        'email_notifications' => true,
                        'browser_notifications' => false,
                        'marketing_updates' => true,
                        'data_sharing_enabled' => true,
                    ],
                ],
            ]);

        $this->assertDatabaseHas('user_settings', [
            'user_id' => $user->id,
            'theme_preference' => 'system',
        ]);
    }

    public function test_settings_update_persists_preferences(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'web')
            ->patchJson('/api/settings', [
                'theme_preference' => 'dark',
                'interface_language' => 'fr',
                'timezone' => 'Europe/Berlin',
                'email_notifications' => false,
                'browser_notifications' => true,
                'marketing_updates' => false,
                'data_sharing_enabled' => false,
            ])
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Settings updated successfully.',
                'data' => [
                    'settings' => [
                        'theme_preference' => 'dark',
                        'interface_language' => 'fr',
                        'timezone' => 'Europe/Berlin',
                        'email_notifications' => false,
                        'browser_notifications' => true,
                        'marketing_updates' => false,
                        'data_sharing_enabled' => false,
                    ],
                ],
            ]);

        $this->assertDatabaseHas('user_settings', [
            'user_id' => $user->id,
            'theme_preference' => 'dark',
            'interface_language' => 'fr',
            'timezone' => 'Europe/Berlin',
            'email_notifications' => false,
            'browser_notifications' => true,
            'marketing_updates' => false,
            'data_sharing_enabled' => false,
        ]);
    }

    public function test_settings_update_rejects_invalid_values(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'web')
            ->patchJson('/api/settings', [
                'theme_preference' => 'blue',
                'interface_language' => 'urdu',
                'timezone' => 'Mars/Phobos',
                'email_notifications' => true,
                'browser_notifications' => false,
                'marketing_updates' => true,
                'data_sharing_enabled' => true,
            ])
            ->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Select a valid theme preference.',
            ])
            ->assertJsonValidationErrors([
                'theme_preference',
                'interface_language',
                'timezone',
            ]);
    }
}
