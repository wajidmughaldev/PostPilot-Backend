<?php

namespace App\Services\Profile;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ProfileService
{
    public function update(User $user, array $data): User
    {
        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'username' => $data['username'] ?: null,
        ]);

        $user->save();

        return $user->fresh();
    }

    public function updateAvatar(User $user, UploadedFile $file): User
    {
        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $path = $file->store('avatars', 'public');

        $user->forceFill([
            'avatar_path' => $path,
        ])->save();

        return $user->fresh();
    }

    public function updatePassword(User $user, array $data): void
    {
        if (! Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Current password is incorrect.'],
            ]);
        }

        $user->forceFill([
            'password' => $data['password'],
        ])->save();
    }
}
