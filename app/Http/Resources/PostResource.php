<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'status' => $this->status,
            'publishMode' => $this->publish_mode,
            'scheduledAt' => $this->scheduled_at?->toISOString(),
            'publishedAt' => $this->published_at?->toISOString(),
            'timezone' => $this->timezone,
            'platforms' => $this->platforms ?? [],
            'accountIds' => $this->account_ids ?? [],
            'accountLabels' => $this->account_labels ?? [],
            'mediaName' => $this->media_name,
            'mediaType' => $this->media_type,
            'visibility' => $this->visibility,
            'ageMin' => $this->age_min,
            'ageMax' => $this->age_max,
            'locations' => $this->locations ?? [],
            'interests' => $this->interests ?? [],
            'lookalikeAudience' => (bool) $this->lookalike_audience,
            'failureReason' => $this->failure_reason,
            'createdAt' => $this->created_at?->toISOString(),
            'updatedAt' => $this->updated_at?->toISOString(),
        ];
    }
}
