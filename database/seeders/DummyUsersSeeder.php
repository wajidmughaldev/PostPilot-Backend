<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\User;
use Illuminate\Database\Seeder;

class DummyUsersSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Abdul Wajid Khan',
                'username' => 'abdulwajid',
                'email' => 'wajid@postpilot-demo.test',
                'password' => 'password123',
                'platform_role' => 'user',
            ],
            [
                'name' => 'Ebad Ali',
                'username' => 'ebadali',
                'email' => 'ebad@postpilot-demo.test',
                'password' => 'password123',
                'platform_role' => 'user',
            ],
            [
                'name' => 'John Content',
                'username' => 'johncontent',
                'email' => 'john@postpilot-demo.test',
                'password' => 'password123',
                'platform_role' => 'user',
            ],
            [
                'name' => 'Sarah Creator',
                'username' => 'sarahcreator',
                'email' => 'sarah@postpilot-demo.test',
                'password' => 'password123',
                'platform_role' => 'user',
            ],
            [
                'name' => 'Admin Support',
                'username' => 'adminsupport',
                'email' => 'admin@postpilot-demo.test',
                'password' => 'password123',
                'platform_role' => 'super_admin',
            ],
        ];

        foreach ($users as $user) {
            User::query()->updateOrCreate(
                ['email' => $user['email']],
                $user,
            );
        }

        $wajid = User::query()->where('email', 'wajid@postpilot-demo.test')->firstOrFail();
        $ebad = User::query()->where('email', 'ebad@postpilot-demo.test')->firstOrFail();
        $john = User::query()->where('email', 'john@postpilot-demo.test')->firstOrFail();
        $sarah = User::query()->where('email', 'sarah@postpilot-demo.test')->firstOrFail();
        $admin = User::query()->where('email', 'admin@postpilot-demo.test')->firstOrFail();

        $creatorHub = Organization::query()->updateOrCreate(
            ['slug' => 'creator-hub-workspace'],
            [
                'name' => 'Creator Hub Workspace',
                'timezone' => 'Asia/Karachi',
                'status' => 'active',
                'contact_person_name' => $wajid->name,
                'contact_person_email' => $wajid->email,
                'contact_person_phone' => '+92 300 1111111',
                'website' => 'https://creator-hub.local',
                'bio' => 'Seeded workspace for testing role-aware posting and team flows.',
                'location' => 'Lahore, Pakistan',
                'industry' => 'Digital Marketing',
                'organization_size' => '11-25',
                'created_by' => $wajid->id,
            ]
        );

        $supportOps = Organization::query()->updateOrCreate(
            ['slug' => 'support-ops-workspace'],
            [
                'name' => 'Support Ops Workspace',
                'timezone' => 'Asia/Karachi',
                'status' => 'active',
                'contact_person_name' => $admin->name,
                'contact_person_email' => $admin->email,
                'contact_person_phone' => '+92 300 2222222',
                'website' => 'https://support-ops.local',
                'bio' => 'Seeded workspace for admin and support testing.',
                'location' => 'Karachi, Pakistan',
                'industry' => 'Operations',
                'organization_size' => '1-10',
                'created_by' => $admin->id,
            ]
        );

        $memberships = [
            [
                'organization_id' => $creatorHub->id,
                'user_id' => $wajid->id,
                'role' => 'owner',
                'created_by' => $wajid->id,
            ],
            [
                'organization_id' => $creatorHub->id,
                'user_id' => $ebad->id,
                'role' => 'manager',
                'created_by' => $wajid->id,
            ],
            [
                'organization_id' => $creatorHub->id,
                'user_id' => $john->id,
                'role' => 'editor',
                'created_by' => $wajid->id,
            ],
            [
                'organization_id' => $creatorHub->id,
                'user_id' => $sarah->id,
                'role' => 'editor',
                'created_by' => $wajid->id,
            ],
            [
                'organization_id' => $supportOps->id,
                'user_id' => $admin->id,
                'role' => 'owner',
                'created_by' => $admin->id,
            ],
        ];

        foreach ($memberships as $membership) {
            OrganizationMember::query()->updateOrCreate(
                [
                    'organization_id' => $membership['organization_id'],
                    'user_id' => $membership['user_id'],
                ],
                [
                    'role' => $membership['role'],
                    'status' => 'active',
                    'created_by' => $membership['created_by'],
                ]
            );
        }
    }
}
