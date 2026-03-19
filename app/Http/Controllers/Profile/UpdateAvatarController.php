<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateAvatarRequest;
use App\Http\Resources\AuthUserResource;
use App\Services\Profile\ProfileService;
use Illuminate\Http\JsonResponse;

class UpdateAvatarController extends Controller
{
    public function __invoke(UpdateAvatarRequest $request, ProfileService $profileService): JsonResponse
    {
        $user = $profileService->updateAvatar($request->user(), $request->file('avatar'));

        return $this->successResponse('Profile photo updated successfully.', [
            'user' => AuthUserResource::make($user)->resolve(),
        ]);
    }
}
