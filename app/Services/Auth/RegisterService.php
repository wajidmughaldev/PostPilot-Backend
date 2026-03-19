<?php

namespace App\Services\Auth;

use App\Http\Resources\AuthUserResource;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RegisterService
{
    public function register(array $data): array
    {
        $user = DB::transaction(fn() => User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'platform_role' => 'user',
        ]));

        return [
            'user' => AuthUserResource::make($user->fresh())->resolve(),
        ];
    }
}
