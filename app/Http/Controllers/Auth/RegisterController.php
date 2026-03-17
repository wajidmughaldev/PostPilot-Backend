<?php

namespace App\Http\Controllers\Auth;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\Auth\RegisterService;
use Illuminate\Http\JsonResponse;

class RegisterController extends Controller
{
    public function __invoke(RegisterRequest $request, RegisterService $registerService): JsonResponse
    {
        // Log::info('Register request data:', $request->all());
        $payload = $registerService->register($request, $request->validated());

        return $this->successResponse('Registration completed successfully.', $payload, 201);
    }
}
