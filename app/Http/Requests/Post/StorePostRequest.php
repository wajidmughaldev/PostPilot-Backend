<?php

namespace App\Http\Requests\Post;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'title' => trim((string) $this->input('title')),
            'content' => trim((string) $this->input('content')),
        ]);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:10000'],
            'mediaName' => ['nullable', 'string', 'max:255'],
            'mediaType' => ['nullable', 'string', 'max:255'],
            'accountIds' => ['required', 'array', 'min:1'],
            'accountIds.*' => ['string', 'max:255'],
            'visibility' => ['required', Rule::in(['public', 'followers'])],
            'ageMin' => ['required', 'string', 'max:16'],
            'ageMax' => ['required', 'string', 'max:16'],
            'locations' => ['required', 'array', 'min:1'],
            'locations.*' => ['string', 'max:255'],
            'interests' => ['required', 'array', 'min:1'],
            'interests.*' => ['string', 'max:255'],
            'lookalikeAudience' => ['required', 'boolean'],
            'publishMode' => ['required', Rule::in(['now', 'schedule', 'draft'])],
            'scheduledAt' => ['nullable', 'date'],
            'timezone' => ['required', 'string', 'max:64'],
        ];
    }
}
