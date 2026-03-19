<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Resources\AuthUserResource;
use App\Services\Profile\ProfileService;
use Illuminate\Http\JsonResponse;

class UpdateProfileController extends Controller
{
    public function __invoke(UpdateProfileRequest $request, ProfileService $profileService): JsonResponse
    {
        $user = $profileService->update($request->user(), $request->validated());

        return $this->successResponse('Profile updated successfully.', [
            'user' => AuthUserResource::make($user)->resolve(),
        ]);
    }
}
