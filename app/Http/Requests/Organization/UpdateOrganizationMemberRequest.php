<?php

namespace App\Http\Requests\Organization;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrganizationMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'role' => ['required', 'in:owner,manager,editor'],
        ];
    }

    public function messages(): array
    {
        return [
            'role.required' => 'Select the new role for this member.',
            'role.in' => 'Role must be owner, manager, or editor.',
        ];
    }
}
