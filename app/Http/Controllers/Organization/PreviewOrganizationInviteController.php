<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Services\Organization\OrganizationMemberService;
use Illuminate\Http\JsonResponse;

class PreviewOrganizationInviteController extends Controller
{
    public function __invoke(string $token, OrganizationMemberService $service): JsonResponse
    {
        return $this->successResponse(
            'Organization invite retrieved successfully.',
            $service->previewInvite($token)
        );
    }
}
