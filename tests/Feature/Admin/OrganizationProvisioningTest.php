<?php

namespace Tests\Feature\Admin;

use App\Models\OrganizationAccessRequest;
use App\Models\User;
use App\Notifications\OrganizationApprovedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrganizationProvisioningTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_organization_from_access_request(): void
    {
        Notification::fake();

        $requester = User::factory()->create([
            'platform_role' => 'user',
        ]);

        $superAdmin = User::factory()->create([
            'platform_role' => 'super_admin',
        ]);

        $accessRequestId = OrganizationAccessRequest::query()->create([
            'user_id' => $requester->id,
            'requested_organization_name' => 'Acme Studio',
            'contact_email' => 'owner@acme.test',
            'status' => 'pending',
        ])->id;

        Sanctum::actingAs($superAdmin);

        $response = $this->postJson('/api/admin/organizations', [
            'organization_access_request_id' => $accessRequestId,
            'name' => 'Acme Studio',
            'review_notes' => 'Approved after support review.',
        ]);

        $response
            ->assertCreated()
            ->assertJson([
                'success' => true,
                'message' => 'Organization created successfully.',
                'data' => [
                    'organization' => [
                        'name' => 'Acme Studio',
                        'status' => 'active',
                    ],
                    'organization_access_request' => [
                        'status' => 'approved',
                    ],
                ],
            ]);

        $this->assertDatabaseHas('organizations', [
            'name' => 'Acme Studio',
            'created_by' => $superAdmin->id,
        ]);

        $this->assertDatabaseHas('organization_members', [
            'user_id' => $requester->id,
            'role' => 'organization_admin',
        ]);

        $this->assertDatabaseHas('organization_access_requests', [
            'id' => $accessRequestId,
            'status' => 'approved',
        ]);

        Notification::assertSentTo($requester, OrganizationApprovedNotification::class);
    }

    public function test_non_super_admin_cannot_create_organization(): void
    {
        $user = User::factory()->create([
            'platform_role' => 'user',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/admin/organizations', [
            'organization_access_request_id' => 1,
            'name' => 'Blocked Org',
        ]);

        $response->assertForbidden();
    }

    public function test_me_endpoint_reflects_organization_context_after_super_admin_provisions_org(): void
    {
        Notification::fake();

        $requester = User::factory()->create([
            'platform_role' => 'user',
        ]);

        $superAdmin = User::factory()->create([
            'platform_role' => 'super_admin',
        ]);

        $accessRequestId = OrganizationAccessRequest::query()->create([
            'user_id' => $requester->id,
            'requested_organization_name' => 'Acme Studio',
            'contact_email' => 'owner@acme.test',
            'status' => 'pending',
        ])->id;

        Sanctum::actingAs($superAdmin);

        $this->postJson('/api/admin/organizations', [
            'organization_access_request_id' => $accessRequestId,
            'name' => 'Acme Studio',
        ])->assertCreated();

        Sanctum::actingAs($requester->fresh());

        $this->getJson('/api/auth/me')
            ->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'organization' => [
                        'name' => 'Acme Studio',
                    ],
                    'onboarding' => [
                        'organization_required' => false,
                        'organization_request_status' => 'approved',
                    ],
                ],
            ]);
    }
}
