<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateUserSettingsRequest;
use App\Http\Resources\UserSettingResource;
use App\Services\Settings\UserSettingsService;
use Illuminate\Http\JsonResponse;

class UpdateUserSettingsController extends Controller
{
    public function __invoke(UpdateUserSettingsRequest $request, UserSettingsService $service): JsonResponse
    {
        $settings = $service->update($request->user(), $request->validated());

        return $this->successResponse('Settings updated successfully.', [
            'settings' => UserSettingResource::make($settings)->resolve(),
        ]);
    }
}
