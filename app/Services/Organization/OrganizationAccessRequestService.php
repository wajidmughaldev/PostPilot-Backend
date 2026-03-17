<?php

namespace App\Services\Organization;

use App\Http\Resources\OrganizationAccessRequestResource;
use App\Models\OrganizationAccessRequest;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class OrganizationAccessRequestService
{
    public function submit(User $user, array $data): array
    {
        $hasPendingRequest = $user->organizationAccessRequests()
            ->where('status', 'pending')
            ->exists();

        if ($hasPendingRequest) {
            throw ValidationException::withMessages([
                'organization_access_request' => ['You already have a pending organization access request.'],
            ]);
        }

        $accessRequest = OrganizationAccessRequest::query()->create([
            'user_id' => $user->id,
            'requested_organization_name' => $data['requested_organization_name'],
            'contact_email' => $data['contact_email'],
            'contact_phone' => $data['contact_phone'] ?? null,
            'website_url' => $data['website_url'] ?? null,
            'message' => $data['message'] ?? null,
            'status' => 'pending',
        ]);

        return [
            'organization_access_request' => OrganizationAccessRequestResource::make($accessRequest)->resolve(),
        ];
    }

    public function listForUser(User $user): array
    {
        return [
            'organization_access_requests' => OrganizationAccessRequestResource::collection(
                $user->organizationAccessRequests()->latest()->get()
            )->resolve(),
        ];
    }

    public function listForAdmin(): array
    {
        return [
            'organization_access_requests' => OrganizationAccessRequestResource::collection(
                OrganizationAccessRequest::query()->with(['user', 'organization'])->latest()->get()
            )->resolve(),
        ];
    }
}
