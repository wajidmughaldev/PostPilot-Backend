<?php

namespace App\Http\Requests\Profile;

use App\Http\Requests\ApiRequest;

class DeleteAccountRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'Current password is required to delete your account.',
        ];
    }
}
