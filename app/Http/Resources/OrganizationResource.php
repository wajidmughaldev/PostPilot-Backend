<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrganizationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $member = $this->relationLoaded('members')
            ? $this->members->firstWhere('user_id', $request->user()?->id)
            : null;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'timezone' => $this->timezone,
            'role' => $member?->role,
            'contactPersonName' => $this->contact_person_name,
            'contactPersonEmail' => $this->contact_person_email,
            'contactPersonPhone' => $this->contact_person_phone,
            'website' => $this->website,
            'bio' => $this->bio,
            'location' => $this->location,
            'industry' => $this->industry,
            'organizationSize' => $this->organization_size,
            'slug' => $this->slug,
            'status' => $this->status,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
