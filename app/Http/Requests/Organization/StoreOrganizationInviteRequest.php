<?php

namespace App\Http\Requests\Organization;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrganizationInviteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => strtolower(trim((string) $this->input('email'))),
        ]);
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'role' => ['required', 'in:owner,manager,editor'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Invite email is required.',
            'email.email' => 'Enter a valid invite email address.',
            'role.required' => 'Select a role for the invited member.',
            'role.in' => 'Role must be owner, manager, or editor.',
        ];
    }
}
