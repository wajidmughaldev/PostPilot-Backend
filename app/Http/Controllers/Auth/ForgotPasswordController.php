<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Services\Auth\ForgotPasswordService;
use Illuminate\Http\JsonResponse;

class ForgotPasswordController extends Controller
{
    public function __invoke(ForgotPasswordRequest $request, ForgotPasswordService $forgotPasswordService): JsonResponse
    {
        $forgotPasswordService->sendResetLink($request->validated('email'));

        return $this->successResponse(
            'If the account exists, a password reset link has been sent to the email address provided.'
        );
    }
}
