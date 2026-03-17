<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Services\Auth\ResetPasswordService;
use Illuminate\Http\JsonResponse;

class ResetPasswordController extends Controller
{
    public function __invoke(ResetPasswordRequest $request, ResetPasswordService $resetPasswordService): JsonResponse
    {
        $resetPasswordService->reset($request->validated());

        return $this->successResponse('Password reset completed successfully.');
    }
}
