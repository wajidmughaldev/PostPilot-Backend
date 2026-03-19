<?php

namespace App\Services\Organization;

use App\Http\Resources\OrganizationAccessRequestResource;
use App\Http\Resources\OrganizationResource;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\OrganizationAccessRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
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
            'requested_organization_name' => $data['name'],
            'contact_person_name' => $data['contact_person_name'],
            'contact_email' => $data['contact_person_email'],
            'contact_phone' => $data['contact_person_phone'] ?? null,
            'timezone' => $data['timezone'],
            'website_url' => $data['website'] ?? null,
            'bio' => $data['bio'] ?? null,
            'location' => $data['location'] ?? null,
            'industry' => $data['industry'],
            'organization_size' => $data['organization_size'],
            'status' => 'pending',
        ]);

        return [
            'organization_access_request' => OrganizationAccessRequestResource::make($accessRequest->load('user'))->resolve(),
        ];
    }

    public function latestForUser(User $user): array
    {
        $latestRequest = $user->organizationAccessRequests()
            ->with(['user', 'reviewer'])
            ->latest()
            ->first();

        return [
            'organization_access_request' => $latestRequest
                ? OrganizationAccessRequestResource::make($latestRequest)->resolve()
                : null,
        ];
    }

    public function listForAdmin(): array
    {
        return [
            'organization_access_requests' => OrganizationAccessRequestResource::collection(
                OrganizationAccessRequest::query()->with(['user', 'organization', 'reviewer'])->latest()->get()
            )->resolve(),
        ];
    }

    public function withdraw(User $user, OrganizationAccessRequest $request): void
    {
        if ((int) $request->user_id !== (int) $user->id) {
            throw ValidationException::withMessages([
                'organization_request' => ['You can only withdraw your own organization request.'],
            ]);
        }

        if ($request->status !== 'pending' && $request->status !== 'rejected') {
            throw ValidationException::withMessages([
                'organization_request' => ['Only pending or rejected requests can be withdrawn.'],
            ]);
        }

        $request->delete();
    }

    public function currentOrganizationForUser(User $user): array
    {
        $member = OrganizationMember::query()
            ->with('organization.members')
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->latest()
            ->first();

        return [
            'organization' => $member?->organization
                ? OrganizationResource::make($member->organization)->resolve(request())
                : null,
        ];
    }

    public function adminOrganizations(): array
    {
        $organizations = Organization::query()
            ->with(['creator', 'members'])
            ->latest()
            ->get()
            ->map(function (Organization $organization): array {
                $ownerMember = $organization->members->firstWhere('role', 'owner')
                    ?? $organization->members->firstWhere('role', 'organization_admin');
                $owner = $ownerMember?->user;

                return [
                    'id' => (string) $organization->id,
                    'name' => $organization->name,
                    'ownerName' => $owner?->name ?? $organization->creator?->name ?? 'Unknown',
                    'ownerEmail' => $owner?->email ?? $organization->creator?->email ?? '',
                    'plan' => 'free',
                    'activeAccounts' => 0,
                    'totalPosts' => 0,
                    'status' => $organization->status === 'active' ? 'active' : 'pending',
                ];
            })
            ->values()
            ->all();

        return [
            'organizations' => $organizations,
        ];
    }

    public function approve(User $superAdmin, OrganizationAccessRequest $request): array
    {
        return DB::transaction(function () use ($superAdmin, $request): array {
            if ($request->status !== 'pending') {
                throw ValidationException::withMessages([
                    'organization_request' => ['Only pending requests can be approved.'],
                ]);
            }

            $organization = Organization::query()->create([
                'name' => $request->requested_organization_name,
                'timezone' => $request->timezone ?? 'UTC',
                'slug' => \Illuminate\Support\Str::slug($request->requested_organization_name) . '-' . $request->id,
                'status' => 'active',
                'contact_person_name' => $request->contact_person_name,
                'contact_person_email' => $request->contact_email,
                'contact_person_phone' => $request->contact_phone,
                'website' => $request->website_url,
                'bio' => $request->bio,
                'location' => $request->location,
                'industry' => $request->industry,
                'organization_size' => $request->organization_size,
                'created_by' => $superAdmin->id,
            ]);

            OrganizationMember::query()->create([
                'organization_id' => $organization->id,
                'user_id' => $request->user_id,
                'role' => 'owner',
                'status' => 'active',
                'created_by' => $superAdmin->id,
            ]);

            $request->update([
                'status' => 'approved',
                'review_notes' => null,
                'reviewed_by' => $superAdmin->id,
                'reviewed_at' => now(),
                'organization_id' => $organization->id,
            ]);

            return [
                'organization_access_request' => OrganizationAccessRequestResource::make($request->fresh(['user', 'reviewer']))->resolve(),
            ];
        });
    }

    public function reject(User $superAdmin, OrganizationAccessRequest $request, string $reason): array
    {
        if ($request->status !== 'pending') {
            throw ValidationException::withMessages([
                'organization_request' => ['Only pending requests can be rejected.'],
            ]);
        }

        $request->update([
            'status' => 'rejected',
            'review_notes' => $reason,
            'reviewed_by' => $superAdmin->id,
            'reviewed_at' => now(),
        ]);

        return [
            'organization_access_request' => OrganizationAccessRequestResource::make($request->fresh(['user', 'reviewer']))->resolve(),
        ];
    }
}
