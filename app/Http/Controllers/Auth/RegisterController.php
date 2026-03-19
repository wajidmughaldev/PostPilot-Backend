<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\Auth\RegisterService;
use Illuminate\Http\JsonResponse;

class RegisterController extends Controller
{
    public function __invoke(RegisterRequest $request, RegisterService $registerService): JsonResponse
    {
        $payload = $registerService->register($request->validated());

        return $this->successResponse(
            'Account created successfully. You can now sign in.',
            $payload,
            201
        );
    }
}
