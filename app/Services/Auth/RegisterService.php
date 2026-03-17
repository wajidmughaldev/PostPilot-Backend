<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RegisterService
{
    public function __construct(
        protected AuthResponseService $authResponseService
    ) {}

    public function register(Request $request, array $data): array
    {
        $user = DB::transaction(fn() => User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]));

        Auth::guard('web')->login($user);

        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        return $this->authResponseService->build($user->fresh());
    }
}
