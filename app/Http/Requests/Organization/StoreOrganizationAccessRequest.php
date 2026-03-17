<?php

namespace App\Http\Requests\Organization;

use App\Http\Requests\ApiRequest;

class StoreOrganizationAccessRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'requested_organization_name' => ['required', 'string', 'max:255'],
            'contact_email' => ['required', 'string', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'message' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
