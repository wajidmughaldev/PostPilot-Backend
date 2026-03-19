<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdatePasswordRequest;
use App\Services\Profile\ProfileService;
use Illuminate\Http\JsonResponse;

class UpdatePasswordController extends Controller
{
    public function __invoke(UpdatePasswordRequest $request, ProfileService $profileService): JsonResponse
    {
        $profileService->updatePassword($request->user(), $request->validated());

        return $this->successResponse('Password updated successfully.');
    }
}
