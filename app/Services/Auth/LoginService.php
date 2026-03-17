<?php

namespace App\Services\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginService
{
    public function __construct(
        protected AuthResponseService $authResponseService
    ) {
    }

    public function authenticate(Request $request, array $credentials): array
    {
        $remember = (bool) ($credentials['remember'] ?? false);

        if (! Auth::guard('web')->attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
        ], $remember)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        return $this->authResponseService->build($request->user()->fresh());
    }
}
