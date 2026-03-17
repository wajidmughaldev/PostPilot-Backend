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

        return [
            'user' => AuthUserResource::make($user)->resolve(),
            'organization' => $membership?->organization
                ? OrganizationResource::make($membership->organization)->resolve()
                : null,
            'onboarding' => [
                'organization_required' => $membership === null,
                'organization_id' => $membership?->organization_id,
                'organization_request_status' => $latestAccessRequest?->status,
            ],
        ];
    }
}
