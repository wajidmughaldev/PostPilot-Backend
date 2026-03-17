<?php

namespace App\Services\Organization;

use App\Http\Resources\OrganizationAccessRequestResource;
use App\Http\Resources\OrganizationResource;
use App\Models\Organization;
use App\Models\OrganizationAccessRequest;
use App\Models\OrganizationMember;
use App\Models\User;
use App\Notifications\OrganizationApprovedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrganizationProvisioningService
{
    public function createFromAccessRequest(User $superAdmin, array $data): array
    {
        return DB::transaction(function () use ($superAdmin, $data): array {
            $accessRequest = OrganizationAccessRequest::query()
                ->with('user')
                ->lockForUpdate()
                ->findOrFail($data['organization_access_request_id']);

            if ($accessRequest->status !== 'pending') {
                throw ValidationException::withMessages([
                    'organization_access_request_id' => ['Only pending organization access requests can be approved.'],
                ]);
            }

            $slug = $this->makeUniqueSlug($data['slug'] ?? Str::slug($data['name']));

            $organization = Organization::query()->create([
                'name' => $data['name'],
                'slug' => $slug,
                'status' => 'active',
                'created_by' => $superAdmin->id,
            ]);

            OrganizationMember::query()->create([
                'organization_id' => $organization->id,
                'user_id' => $accessRequest->user_id,
                'role' => 'organization_admin',
                'status' => 'active',
                'created_by' => $superAdmin->id,
            ]);

            $accessRequest->update([
                'status' => 'approved',
                'review_notes' => $data['review_notes'] ?? null,
                'reviewed_by' => $superAdmin->id,
                'reviewed_at' => now(),
                'organization_id' => $organization->id,
            ]);

            $accessRequest->user->notify(new OrganizationApprovedNotification($organization));

            return [
                'organization' => OrganizationResource::make($organization)->resolve(),
                'organization_access_request' => OrganizationAccessRequestResource::make($accessRequest->fresh())->resolve(),
            ];
        });
    }

    protected function makeUniqueSlug(string $slug): string
    {
        $baseSlug = $slug !== '' ? $slug : Str::slug(Str::random(8));
        $candidate = $baseSlug;
        $counter = 2;

        while (Organization::query()->where('slug', $candidate)->exists()) {
            $candidate = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $candidate;
    }
}
