<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class OrganizationMemberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'role' => $this->role,
            'status' => $this->status,
            'joinedAt' => $this->created_at?->toISOString(),
            'isCurrentUser' => (int) $this->user_id === (int) $request->user()?->id,
            'user' => [
                'id' => (string) $this->user?->id,
                'name' => $this->user?->name,
                'username' => $this->user?->username,
                'email' => $this->user?->email,
                'avatar_url' => $this->user?->avatar_path ? Storage::disk('public')->url($this->user->avatar_path) : null,
            ],
        ];
    }
}
