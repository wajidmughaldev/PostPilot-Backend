<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\Auth\LoginService;
use Illuminate\Http\JsonResponse;

class LoginController extends Controller
{
    public function __invoke(LoginRequest $request, LoginService $loginService): JsonResponse
    {
        $payload = $loginService->authenticate($request, $request->validated());

        return $this->successResponse('Login completed successfully.', $payload);
    }
}
