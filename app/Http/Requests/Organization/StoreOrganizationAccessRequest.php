<?php

namespace App\Http\Requests\Organization;

use App\Http\Requests\ApiRequest;

class StoreOrganizationAccessRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'contact_person_name' => ['required', 'string', 'max:255'],
            'contact_person_email' => ['required', 'string', 'email', 'max:255'],
            'contact_person_phone' => ['nullable', 'string', 'max:50'],
            'timezone' => ['required', 'string', 'max:100'],
            'website' => ['nullable', 'url', 'max:255'],
            'bio' => ['nullable', 'string', 'max:5000'],
            'location' => ['nullable', 'string', 'max:255'],
            'industry' => ['required', 'string', 'max:255'],
            'organization_size' => ['required', 'string', 'max:32'],
        ];
    }
}
