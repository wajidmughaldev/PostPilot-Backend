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

        $response = $this->actingAs($user, 'web')->postJson('/api/organization-access-requests', [
            'requested_organization_name' => 'Acme Studio',
            'contact_email' => 'owner@acme.test',
            'contact_phone' => '1234567890',
            'website_url' => 'https://acme.test',
            'message' => 'We need access for our marketing team.',
        ]);

        $response
            ->assertCreated()
            ->assertJson([
                'success' => true,
                'message' => 'Organization access request submitted successfully.',
                'data' => [
                    'organization_access_request' => [
                        'requested_organization_name' => 'Acme Studio',
                        'contact_email' => 'owner@acme.test',
                        'status' => 'pending',
                    ],
                ],
            ]);

        $this->assertDatabaseHas('organization_access_requests', [
            'user_id' => $user->id,
            'requested_organization_name' => 'Acme Studio',
            'status' => 'pending',
        ]);
    }

    public function test_user_cannot_create_multiple_pending_organization_access_requests(): void
    {
        $user = User::factory()->create();

        OrganizationAccessRequest::query()->create([
            'user_id' => $user->id,
            'requested_organization_name' => 'Acme Studio',
            'contact_email' => 'owner@acme.test',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user, 'web')->postJson('/api/organization-access-requests', [
            'requested_organization_name' => 'Second Request',
            'contact_email' => 'owner@acme.test',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['organization_access_request']);
    }

    public function test_authenticated_user_can_list_their_organization_access_requests(): void
    {
        $user = User::factory()->create();

        OrganizationAccessRequest::query()->create([
            'user_id' => $user->id,
            'requested_organization_name' => 'Acme Studio',
            'contact_email' => 'owner@acme.test',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user, 'web')->getJson('/api/organization-access-requests/me');

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Organization access requests retrieved successfully.',
            ]);
    }
}
