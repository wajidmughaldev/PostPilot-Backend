<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserSettingResource;
use App\Services\Settings\UserSettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShowUserSettingsController extends Controller
{
    public function __invoke(Request $request, UserSettingsService $service): JsonResponse
    {
        $settings = $service->getForUser($request->user());

        return $this->successResponse('Settings retrieved successfully.', [
            'settings' => UserSettingResource::make($settings)->resolve(),
        ]);
    }
}
