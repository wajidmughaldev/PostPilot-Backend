<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserSettingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'theme_preference' => $this->theme_preference,
            'interface_language' => $this->interface_language,
            'timezone' => $this->timezone,
            'email_notifications' => $this->email_notifications,
            'browser_notifications' => $this->browser_notifications,
            'marketing_updates' => $this->marketing_updates,
            'data_sharing_enabled' => $this->data_sharing_enabled,
        ];
    }
}
