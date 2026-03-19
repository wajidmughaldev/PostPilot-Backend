<?php

namespace Tests\Feature\Organization;

use App\Models\Organization;
use App\Models\OrganizationInvite;
use App\Models\OrganizationMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationMemberManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_view_current_team_members_and_pending_invites(): void
    {
        [$owner, $organization] = $this->createOwnedOrganization();
        $member = User::factory()->create();
        $inviter = User::factory()->create();

        OrganizationMember::query()->create([
            'organization_id' => $organization->id,
            'user_id' => $member->id,
            'role' => 'editor',
            'status' => 'active',
            'created_by' => $owner->id,
        ]);

        OrganizationInvite::query()->create([
            'organization_id' => $organization->id,
            'email' => 'invitee@example.com',
            'role' => 'manager',
            'token' => 'invite-token-123',
            'status' => 'pending',
            'invited_by' => $inviter->id,
            'expires_at' => now()->addDays(7),
        ]);

        $this->actingAs($owner, 'web')
            ->getJson('/api/organizations/current/team')
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Organization team retrieved successfully.',
                'data' => [
                    'members' => [
                        ['role' => 'owner'],
                        ['role' => 'editor'],
                    ],
                    'invites' => [
                        ['email' => 'invitee@example.com', 'role' => 'manager'],
                    ],
                ],
            ]);
    }

    public function test_owner_can_invite_member(): void
    {
        [$owner, $organization] = $this->createOwnedOrganization();

        $response = $this->actingAs($owner, 'web')->postJson('/api/organizations/current/invites', [
            'email' => 'newmember@example.com',
            'role' => 'editor',
        ]);

        $response
            ->assertCreated()
            ->assertJson([
                'success' => true,
                'message' => 'Organization invite sent successfully.',
                'data' => [
                    'invite' => [
                        'email' => 'newmember@example.com',
                        'role' => 'editor',
                        'status' => 'pending',
                    ],
                ],
            ]);

        $this->assertDatabaseHas('organization_invites', [
            'organization_id' => $organization->id,
            'email' => 'newmember@example.com',
            'role' => 'editor',
            'status' => 'pending',
        ]);
    }

    public function test_non_owner_cannot_invite_members(): void
    {
        $owner = User::factory()->create();
        $manager = User::factory()->create();
        $organization = Organization::query()->create([
            'name' => 'Acme Org',
            'timezone' => 'UTC',
            'slug' => 'acme-org',
            'status' => 'active',
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
            'user_id' => $manager->id,
            'role' => 'manager',
            'status' => 'active',
            'created_by' => $owner->id,
        ]);

        $this->actingAs($manager, 'web')
            ->postJson('/api/organizations/current/invites', [
                'email' => 'blocked@example.com',
                'role' => 'editor',
            ])
            ->assertForbidden();
    }

    public function test_owner_can_update_member_role_and_remove_member(): void
    {
        [$owner, $organization] = $this->createOwnedOrganization();
        $member = User::factory()->create();

        $organizationMember = OrganizationMember::query()->create([
            'organization_id' => $organization->id,
            'user_id' => $member->id,
            'role' => 'editor',
            'status' => 'active',
            'created_by' => $owner->id,
        ]);

        $this->actingAs($owner, 'web')
            ->patchJson("/api/organizations/current/members/{$organizationMember->id}", [
                'role' => 'manager',
            ])
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Organization member updated successfully.',
                'data' => [
                    'member' => [
                        'role' => 'manager',
                    ],
                ],
            ]);

        $this->assertDatabaseHas('organization_members', [
            'id' => $organizationMember->id,
            'role' => 'manager',
        ]);

        $this->actingAs($owner, 'web')
            ->deleteJson("/api/organizations/current/members/{$organizationMember->id}")
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Organization member removed successfully.',
                'data' => null,
            ]);

        $this->assertDatabaseMissing('organization_members', [
            'id' => $organizationMember->id,
        ]);
    }

    public function test_invite_can_be_previewed_and_accepted_by_new_user(): void
    {
        [$owner, $organization] = $this->createOwnedOrganization();

        $invite = OrganizationInvite::query()->create([
            'organization_id' => $organization->id,
            'email' => 'joiner@example.com',
            'role' => 'editor',
            'token' => 'team-invite-token',
            'status' => 'pending',
            'invited_by' => $owner->id,
            'expires_at' => now()->addDays(7),
        ]);

        $this->getJson("/api/invites/{$invite->token}")
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Organization invite retrieved successfully.',
                'data' => [
                    'organizationName' => 'Acme Org',
                    'invitedEmail' => 'joiner@example.com',
                    'role' => 'editor',
                ],
            ]);

        $this->postJson("/api/invites/{$invite->token}/accept", [
            'name' => 'Joiner User',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Organization invite accepted successfully.',
                'data' => [
                    'user' => [
                        'email' => 'joiner@example.com',
                    ],
                    'organizationAccess' => [
                        'hasOrganization' => true,
                        'organizationRole' => 'editor',
                    ],
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'joiner@example.com',
            'name' => 'Joiner User',
        ]);

        $this->assertDatabaseHas('organization_members', [
            'organization_id' => $organization->id,
            'role' => 'editor',
        ]);

        $this->assertDatabaseHas('organization_invites', [
            'id' => $invite->id,
            'status' => 'accepted',
        ]);
    }

    protected function createOwnedOrganization(): array
    {
        $owner = User::factory()->create();

        $organization = Organization::query()->create([
            'name' => 'Acme Org',
            'timezone' => 'UTC',
            'slug' => 'acme-org',
            'status' => 'active',
            'created_by' => $owner->id,
        ]);

        OrganizationMember::query()->create([
            'organization_id' => $organization->id,
            'user_id' => $owner->id,
            'role' => 'owner',
            'status' => 'active',
            'created_by' => $owner->id,
        ]);

        return [$owner, $organization];
    }
}
