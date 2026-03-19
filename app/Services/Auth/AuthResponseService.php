<?php

namespace App\Services\Auth;

use App\Http\Resources\AuthUserResource;
use App\Http\Resources\OrganizationResource;
use App\Models\User;

class AuthResponseService
{
    public function build(User $user): array
    {
        $membership = $user->organizationMembers()
            ->with('organization')
            ->where('status', 'active')
            ->latest()
            ->first();

        $latestAccessRequest = $user->organizationAccessRequests()
            ->latest()
            ->first();

        $organization = $membership?->organization
            ? OrganizationResource::make($membership->organization)->resolve()
            : null;

        $organizationRole = $membership?->role;

        return [
            'user' => AuthUserResource::make($user)->resolve(),
            'platformRole' => $user->platform_role ?? 'user',
            'organizationAccess' => [
                'hasOrganization' => $membership !== null,
                'organizationId' => $membership?->organization_id,
                'organizationName' => $membership?->organization?->name,
                'organizationRole' => $organizationRole,
                'permissions' => [
                    'canViewAllPosts' => in_array($organizationRole, ['owner', 'manager'], true),
                    'canManageAllPosts' => in_array($organizationRole, ['owner', 'manager'], true),
                    'canConnectAccounts' => in_array($organizationRole, ['owner', 'manager'], true),
                    'canDisconnectAccounts' => in_array($organizationRole, ['owner', 'manager'], true),
                    'canInviteMembers' => $organizationRole === 'owner',
                    'canViewLogs' => in_array($organizationRole, ['owner', 'manager'], true),
                    'canViewAnalytics' => in_array($organizationRole, ['owner', 'manager'], true),
                    'canUpdateOrganizationInfo' => $organizationRole === 'owner',
                    'canDeleteOrganization' => $organizationRole === 'owner',
                ],
            ],
            'organization' => $organization,
            'onboarding' => [
                'organization_required' => $membership === null,
                'organization_id' => $membership?->organization_id,
                'organization_request_status' => $latestAccessRequest?->status,
            ],
        ];
    }
}
