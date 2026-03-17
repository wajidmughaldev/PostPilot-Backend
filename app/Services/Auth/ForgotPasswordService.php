<?php

namespace App\Services\Auth;

use Illuminate\Support\Facades\Password;

class ForgotPasswordService
{
    public function sendResetLink(string $email): void
    {
        Password::broker()->sendResetLink([
            'email' => $email,
        ]);
    }
}
