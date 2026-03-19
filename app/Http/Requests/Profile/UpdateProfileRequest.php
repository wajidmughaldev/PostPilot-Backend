<?php

namespace App\Http\Requests\Profile;

use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends ApiRequest
{
    protected function prepareForValidation(): void
    {
        $name = $this->input('name');
        $email = $this->input('email');
        $username = $this->input('username');

        $this->merge([
            'name' => is_string($name) ? trim(preg_replace('/\s+/', ' ', $name) ?? $name) : $name,
            'email' => is_string($email) ? mb_strtolower(trim($email)) : $email,
            'username' => is_string($username) ? mb_strtolower(trim(ltrim($username, '@'))) : $username,
        ]);
    }

    public function rules(): array
    {
        $userId = $this->user()?->getKey();

        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'string', 'email:rfc', Rule::unique('users', 'email')->ignore($userId)],
            'username' => ['nullable', 'string', 'min:3', 'max:50', 'alpha_dash', Rule::unique('users', 'username')->ignore($userId)],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Display name is required.',
            'name.min' => 'Display name must be at least 2 characters.',
            'email.required' => 'Email is required.',
            'email.email' => 'Enter a valid email address.',
            'email.unique' => 'An account with this email already exists.',
            'username.min' => 'Username must be at least 3 characters.',
            'username.alpha_dash' => 'Username can only contain letters, numbers, dashes, and underscores.',
            'username.unique' => 'This username is already taken.',
        ];
    }
}
