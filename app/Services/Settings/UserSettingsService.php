<?php

namespace App\Services\Settings;

use App\Models\User;
use App\Models\UserSetting;

class UserSettingsService
{
    public function getForUser(User $user): UserSetting
    {
        return $user->settings()->firstOrCreate([], $this->defaultAttributes());
    }

    public function update(User $user, array $data): UserSetting
    {
        $settings = $this->getForUser($user);

        $settings->fill([
            'theme_preference' => $data['theme_preference'],
            'interface_language' => $data['interface_language'],
            'timezone' => $data['timezone'],
            'email_notifications' => $data['email_notifications'],
            'browser_notifications' => $data['browser_notifications'],
            'marketing_updates' => $data['marketing_updates'],
            'data_sharing_enabled' => $data['data_sharing_enabled'],
        ]);

        $settings->save();

        return $settings->fresh();
    }

    private function defaultAttributes(): array
    {
        return [
            'theme_preference' => 'system',
            'interface_language' => 'en-US',
            'timezone' => 'UTC',
            'email_notifications' => true,
            'browser_notifications' => false,
            'marketing_updates' => true,
            'data_sharing_enabled' => true,
        ];
    }
}
