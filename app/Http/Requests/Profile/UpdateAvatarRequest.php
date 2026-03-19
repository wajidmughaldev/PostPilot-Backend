<?php

namespace App\Http\Requests\Profile;

use App\Http\Requests\ApiRequest;

class UpdateAvatarRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'avatar.required' => 'Profile photo is required.',
            'avatar.image' => 'Profile photo must be an image file.',
            'avatar.mimes' => 'Profile photo must be a JPG or PNG image.',
            'avatar.max' => 'Profile photo must be 5MB or smaller.',
        ];
    }
}
