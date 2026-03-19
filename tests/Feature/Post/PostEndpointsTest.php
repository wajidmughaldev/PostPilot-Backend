<?php

namespace Tests\Feature\Post;

use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_personal_user_can_create_and_list_own_posts(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'web')
            ->postJson('/api/me/posts', $this->payload([
                'accountIds' => ['demo-instagram-account'],
                'publishMode' => 'draft',
            ]))
            ->assertCreated()
            ->assertJson([
                'success' => true,
                'message' => 'Post created successfully.',
                'data' => [
                    'status' => 'draft',
                    'accountLabels' => ['@social_maven'],
                ],
            ]);

        $this->actingAs($user, 'web')
            ->getJson('/api/me/posts')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_owner_can_view_all_posts_in_current_organization(): void
    {
        [$organization, $owner, $editor] = $this->organizationWithUsers();

        Post::query()->create($this->dbPost($owner, $organization->id, 'Owner Post'));
        Post::query()->create($this->dbPost($editor, $organization->id, 'Editor Post'));

        $this->actingAs($owner, 'web')
            ->getJson('/api/organizations/current/posts')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_editor_only_sees_own_posts_in_current_organization(): void
    {
        [$organization, $owner, $editor] = $this->organizationWithUsers();

        Post::query()->create($this->dbPost($owner, $organization->id, 'Owner Post'));
        Post::query()->create($this->dbPost($editor, $organization->id, 'Editor Post'));

        $this->actingAs($editor, 'web')
            ->getJson('/api/organizations/current/posts')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'title' => 'Editor Post',
            ]);
    }

    public function test_owner_can_update_any_organization_post(): void
    {
        [$organization, $owner, $editor] = $this->organizationWithUsers();

        $post = Post::query()->create($this->dbPost($editor, $organization->id, 'Before Update'));

        $this->actingAs($owner, 'web')
            ->patchJson("/api/organizations/current/posts/{$post->id}", $this->payload([
                'title' => 'After Update',
                'accountIds' => ['demo-facebook-account'],
            ]))
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Post updated successfully.',
                'data' => [
                    'title' => 'After Update',
                    'accountLabels' => ['Acme Marketing'],
                ],
            ]);
    }

    public function test_editor_cannot_update_another_members_organization_post(): void
    {
        [$organization, $owner, $editor] = $this->organizationWithUsers();

        $post = Post::query()->create($this->dbPost($owner, $organization->id, 'Owner Post'));

        $this->actingAs($editor, 'web')
            ->patchJson("/api/organizations/current/posts/{$post->id}", $this->payload([
                'title' => 'Blocked Update',
            ]))
            ->assertNotFound();
    }

    public function test_save_draft_sets_post_back_to_draft(): void
    {
        $user = User::factory()->create();
        $post = Post::query()->create($this->dbPost($user, null, 'Scheduled Post', [
            'status' => 'scheduled',
            'publish_mode' => 'schedule',
            'scheduled_at' => now()->addDay(),
        ]));

        $this->actingAs($user, 'web')
            ->postJson("/api/me/posts/{$post->id}/save-draft")
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Post moved to draft successfully.',
                'data' => [
                    'status' => 'draft',
                    'publishMode' => 'draft',
                ],
            ]);
    }

    private function organizationWithUsers(): array
    {
        $owner = User::factory()->create();
        $editor = User::factory()->create();

        $organization = Organization::query()->create([
            'name' => 'Acme Org',
            'timezone' => 'Asia/Karachi',
            'slug' => 'acme-org',
            'status' => 'active',
            'contact_person_name' => 'Owner',
            'contact_person_email' => $owner->email,
            'industry' => 'Marketing Agency',
            'organization_size' => '11-25',
            'created_by' => $owner->id,
        ]);

        OrganizationMember::query()->create([
            'organization_id' => $organization->id,
            'user_id' => $owner->id,
            'role' => 'owner',
            'status' => 'active',
            'created_by' => $owner->id,
        ]);

        OrganizationMember::query()->create([
            'organization_id' => $organization->id,
            'user_id' => $editor->id,
            'role' => 'editor',
            'status' => 'active',
            'created_by' => $owner->id,
        ]);

        return [$organization, $owner, $editor];
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Sample Post',
            'content' => 'This is a sample post body.',
            'mediaName' => 'sample.png',
            'mediaType' => 'image/png',
            'accountIds' => ['demo-instagram-account'],
            'visibility' => 'public',
            'ageMin' => '18',
            'ageMax' => '45',
            'locations' => ['United States'],
            'interests' => ['Digital Marketing'],
            'lookalikeAudience' => false,
            'publishMode' => 'schedule',
            'scheduledAt' => now()->addDay()->toISOString(),
            'timezone' => 'UTC',
        ], $overrides);
    }

    private function dbPost(User $user, ?int $organizationId, string $title, array $overrides = []): array
    {
        return array_merge([
            'user_id' => $user->id,
            'organization_id' => $organizationId,
            'title' => $title,
            'content' => 'Stored post content.',
            'status' => 'draft',
            'publish_mode' => 'draft',
            'scheduled_at' => null,
            'published_at' => null,
            'timezone' => 'UTC',
            'platforms' => ['instagram'],
            'account_ids' => ['demo-instagram-account'],
            'account_labels' => ['@social_maven'],
            'media_name' => 'asset.png',
            'media_type' => 'image/png',
            'visibility' => 'public',
            'age_min' => '18',
            'age_max' => '45',
            'locations' => ['United States'],
            'interests' => ['Digital Marketing'],
            'lookalike_audience' => false,
            'failure_reason' => null,
        ], $overrides);
    }
}
