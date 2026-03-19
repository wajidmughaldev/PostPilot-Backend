<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'theme_preference' => ['required', Rule::in(['system', 'light', 'dark'])],
            'interface_language' => ['required', Rule::in(['en-US', 'es', 'fr', 'de'])],
            'timezone' => ['required', Rule::in([
                'UTC',
                'America/Los_Angeles',
                'America/New_York',
                'Europe/Berlin',
            ])],
            'email_notifications' => ['required', 'boolean'],
            'browser_notifications' => ['required', 'boolean'],
            'marketing_updates' => ['required', 'boolean'],
            'data_sharing_enabled' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'theme_preference.in' => 'Select a valid theme preference.',
            'interface_language.in' => 'Select a valid interface language.',
            'timezone.in' => 'Select a valid timezone.',
        ];
    }
}
