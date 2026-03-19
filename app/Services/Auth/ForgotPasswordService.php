<?php

namespace App\Services\Auth;

use RuntimeException;
use Throwable;
use Illuminate\Support\Facades\Password;

class ForgotPasswordService
{
    public function sendResetLink(string $email): void
    {
        try {
            Password::broker()->sendResetLink([
                'email' => $email,
            ]);
        } catch (Throwable $exception) {
            throw new RuntimeException(
                'Password reset email service is currently unavailable. Please try again later.',
                previous: $exception
            );
        }
    }
}
