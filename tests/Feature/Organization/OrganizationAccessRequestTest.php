<?php

namespace Tests\Feature\Organization;

use App\Models\OrganizationAccessRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationAccessRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_submit_organization_access_request(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'web')->postJson('/api/organization-requests', [
            'name' => 'Acme Studio',
            'contact_person_name' => 'Owner Name',
            'contact_person_email' => 'owner@acme.test',
            'contact_person_phone' => '1234567890',
            'timezone' => 'Asia/Karachi',
            'website' => 'https://acme.test',
            'bio' => 'We need access for our marketing team.',
            'location' => 'Karachi',
            'industry' => 'Marketing Agency',
            'organization_size' => '11-25',
        ]);

        $response
            ->assertCreated()
            ->assertJson([
                'success' => true,
                'message' => 'Organization request submitted successfully.',
                'data' => [
                    'name' => 'Acme Studio',
                    'contactPersonEmail' => 'owner@acme.test',
                    'status' => 'pending',
                ],
            ]);

        $this->assertDatabaseHas('organization_access_requests', [
            'user_id' => $user->id,
            'requested_organization_name' => 'Acme Studio',
            'contact_person_name' => 'Owner Name',
            'timezone' => 'Asia/Karachi',
            'status' => 'pending',
        ]);
    }

    public function test_user_cannot_create_multiple_pending_organization_access_requests(): void
    {
        $user = User::factory()->create();

        OrganizationAccessRequest::query()->create([
            'user_id' => $user->id,
            'requested_organization_name' => 'Acme Studio',
            'contact_person_name' => 'Owner Name',
            'contact_email' => 'owner@acme.test',
            'timezone' => 'Asia/Karachi',
            'industry' => 'Marketing Agency',
            'organization_size' => '11-25',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user, 'web')->postJson('/api/organization-requests', [
            'name' => 'Second Request',
            'contact_person_name' => 'Owner Name',
            'contact_person_email' => 'owner@acme.test',
            'timezone' => 'Asia/Karachi',
            'industry' => 'Marketing Agency',
            'organization_size' => '11-25',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['organization_access_request']);
    }

    public function test_authenticated_user_can_get_latest_organization_access_request(): void
    {
        $user = User::factory()->create();

        OrganizationAccessRequest::query()->create([
            'user_id' => $user->id,
            'requested_organization_name' => 'Acme Studio',
            'contact_person_name' => 'Owner Name',
            'contact_email' => 'owner@acme.test',
            'timezone' => 'Asia/Karachi',
            'industry' => 'Marketing Agency',
            'organization_size' => '11-25',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user, 'web')->getJson('/api/organization-requests/latest');

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Latest organization request retrieved successfully.',
                'data' => [
                    'name' => 'Acme Studio',
                    'status' => 'pending',
                ],
            ]);
    }

    public function test_authenticated_user_can_withdraw_their_request(): void
    {
        $user = User::factory()->create();

        $request = OrganizationAccessRequest::query()->create([
            'user_id' => $user->id,
            'requested_organization_name' => 'Acme Studio',
            'contact_person_name' => 'Owner Name',
            'contact_email' => 'owner@acme.test',
            'timezone' => 'Asia/Karachi',
            'industry' => 'Marketing Agency',
            'organization_size' => '11-25',
            'status' => 'pending',
        ]);

        $this->actingAs($user, 'web')
            ->deleteJson("/api/organization-requests/{$request->id}")
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Organization request withdrawn successfully.',
                'data' => null,
            ]);

        $this->assertDatabaseMissing('organization_access_requests', [
            'id' => $request->id,
        ]);
    }
}
