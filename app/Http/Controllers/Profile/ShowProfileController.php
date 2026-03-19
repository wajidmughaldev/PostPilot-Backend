<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Http\Resources\AuthUserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShowProfileController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        return $this->successResponse('Profile retrieved successfully.', [
            'user' => AuthUserResource::make($request->user())->resolve(),
        ]);
    }
}
