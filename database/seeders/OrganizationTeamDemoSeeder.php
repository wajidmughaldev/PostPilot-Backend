<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrganizationTeamDemoSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::query()->updateOrCreate(
            ['email' => 'owner@postpilot-demo.test'],
            [
                'name' => 'Demo Owner',
                'username' => 'demoowner',
                'password' => 'password123',
                'platform_role' => 'user',
            ]
        );

        $manager = User::query()->updateOrCreate(
            ['email' => 'manager@postpilot-demo.test'],
            [
                'name' => 'Demo Manager',
                'username' => 'demomanager',
                'password' => 'password123',
                'platform_role' => 'user',
            ]
        );

        $editor = User::query()->updateOrCreate(
            ['email' => 'editor@postpilot-demo.test'],
            [
                'name' => 'Demo Editor',
                'username' => 'demoeditor',
                'password' => 'password123',
                'platform_role' => 'user',
            ]
        );

        $organization = Organization::query()->updateOrCreate(
            ['slug' => 'demo-team-workspace'],
            [
                'name' => 'Demo Team Workspace',
                'timezone' => 'Asia/Karachi',
                'status' => 'active',
                'contact_person_name' => $owner->name,
                'contact_person_email' => $owner->email,
                'contact_person_phone' => '+92 300 0000000',
                'website' => 'https://demo-team.local',
                'bio' => 'Seeded organization for testing team roles and invite flows.',
                'location' => 'Karachi, Pakistan',
                'industry' => 'Marketing Agency',
                'organization_size' => '11-25',
                'created_by' => $owner->id,
            ]
        );

        OrganizationMember::query()->updateOrCreate(
            [
                'organization_id' => $organization->id,
                'user_id' => $owner->id,
            ],
            [
                'role' => 'owner',
                'status' => 'active',
                'created_by' => $owner->id,
            ]
        );

        OrganizationMember::query()->updateOrCreate(
            [
                'organization_id' => $organization->id,
                'user_id' => $manager->id,
            ],
            [
                'role' => 'manager',
                'status' => 'active',
                'created_by' => $owner->id,
            ]
        );

        OrganizationMember::query()->updateOrCreate(
            [
                'organization_id' => $organization->id,
                'user_id' => $editor->id,
            ],
            [
                'role' => 'editor',
                'status' => 'active',
                'created_by' => $owner->id,
            ]
        );
    }
}
