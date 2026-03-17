<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

class CreateOrganizationFromRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'organization_access_request_id' => ['required', 'integer', 'exists:organization_access_requests,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('organizations', 'slug')],
            'review_notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
