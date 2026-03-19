<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrganizationAccessRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'name' => $this->requested_organization_name,
            'contactPersonName' => $this->contact_person_name,
            'contactPersonEmail' => $this->contact_email,
            'contactPersonPhone' => $this->contact_phone,
            'timezone' => $this->timezone,
            'website' => $this->website_url,
            'bio' => $this->bio,
            'location' => $this->location,
            'industry' => $this->industry,
            'organizationSize' => $this->organization_size,
            'requestedById' => (string) $this->user_id,
            'requestedByName' => $this->whenLoaded('user', fn () => $this->user?->name, $this->user?->name),
            'requestedByEmail' => $this->whenLoaded('user', fn () => $this->user?->email, $this->user?->email),
            'requestedAt' => $this->created_at?->toISOString(),
            'status' => $this->status,
            'rejectionReason' => $this->review_notes,
            'reviewedByName' => $this->whenLoaded('reviewer', fn () => $this->reviewer?->name, $this->reviewer?->name),
            'reviewedAt' => $this->reviewed_at?->toISOString(),
        ];
    }
}
