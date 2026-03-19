<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrganizationInviteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $frontendUrl = rtrim((string) config('app.frontend_url'), '/');

        return [
            'id' => (string) $this->id,
            'email' => $this->email,
            'role' => $this->role,
            'status' => $this->status,
            'inviteUrl' => sprintf('%s/invite/%s', $frontendUrl, $this->token),
            'invitedByName' => $this->inviter?->name,
            'createdAt' => $this->created_at?->toISOString(),
            'expiresAt' => $this->expires_at?->toISOString(),
        ];
    }
}
