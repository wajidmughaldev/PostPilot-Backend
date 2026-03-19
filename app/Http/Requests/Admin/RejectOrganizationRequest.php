<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\ApiRequest;

class RejectOrganizationRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'max:5000'],
        ];
    }
}
