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
        $requester = User::factory()->create([
            'platform_role' => 'user',
        ]);

        $superAdmin = User::factory()->create([
            'platform_role' => 'super_admin',
        ]);

        $accessRequestId = OrganizationAccessRequest::query()->create([
            'user_id' => $requester->id,
            'requested_organization_name' => 'Acme Studio',
            'contact_person_name' => 'Owner Name',
            'contact_email' => 'owner@acme.test',
            'timezone' => 'Asia/Karachi',
            'industry' => 'Marketing Agency',
            'organization_size' => '11-25',
            'status' => 'pending',
        ])->id;

        Sanctum::actingAs($superAdmin);

        $response = $this->postJson("/api/admin/organization-requests/{$accessRequestId}/approve");

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Organization request approved successfully.',
                'data' => [
                    'name' => 'Acme Studio',
                    'status' => 'approved',
                ],
            ]);

        $this->assertDatabaseHas('organizations', [
            'name' => 'Acme Studio',
            'created_by' => $superAdmin->id,
        ]);

        $this->assertDatabaseHas('organization_members', [
            'user_id' => $requester->id,
            'role' => 'owner',
        ]);

        $this->assertDatabaseHas('organization_access_requests', [
            'id' => $accessRequestId,
            'status' => 'approved',
        ]);
    }

    public function test_non_super_admin_cannot_create_organization(): void
    {
        $user = User::factory()->create([
            'platform_role' => 'user',
        ]);

        $accessRequestId = OrganizationAccessRequest::query()->create([
            'user_id' => $user->id,
            'requested_organization_name' => 'Blocked Org',
            'contact_person_name' => 'Owner Name',
            'contact_email' => 'owner@blocked.test',
            'timezone' => 'Asia/Karachi',
            'industry' => 'Marketing Agency',
            'organization_size' => '11-25',
            'status' => 'pending',
        ])->id;

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/admin/organization-requests/{$accessRequestId}/approve");

        $response->assertForbidden();
    }

    public function test_super_admin_can_reject_organization_request(): void
    {
        $requester = User::factory()->create([
            'platform_role' => 'user',
        ]);

        $superAdmin = User::factory()->create([
            'platform_role' => 'super_admin',
        ]);

        $accessRequestId = OrganizationAccessRequest::query()->create([
            'user_id' => $requester->id,
            'requested_organization_name' => 'Acme Studio',
            'contact_person_name' => 'Owner Name',
            'contact_email' => 'owner@acme.test',
            'timezone' => 'Asia/Karachi',
            'industry' => 'Marketing Agency',
            'organization_size' => '11-25',
            'status' => 'pending',
        ])->id;

        Sanctum::actingAs($superAdmin);

        $this->postJson("/api/admin/organization-requests/{$accessRequestId}/reject", [
            'reason' => 'Please provide a clearer company website.',
        ])
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Organization request rejected successfully.',
                'data' => [
                    'status' => 'rejected',
                    'rejectionReason' => 'Please provide a clearer company website.',
                ],
            ]);

        $this->assertDatabaseHas('organization_access_requests', [
            'id' => $accessRequestId,
            'status' => 'rejected',
            'review_notes' => 'Please provide a clearer company website.',
        ]);
    }
}
