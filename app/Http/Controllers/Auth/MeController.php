<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\AuthResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeController extends Controller
{
    public function __invoke(Request $request, AuthResponseService $authResponseService): JsonResponse
    {
        if (! $request->user()) {
            return $this->successResponse('No authenticated user found.', null);
        }

        return $this->successResponse(
            'Authenticated user retrieved successfully.',
            $authResponseService->build($request->user())
        );
    }
}
