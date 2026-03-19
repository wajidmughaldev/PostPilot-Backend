<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Services\Organization\OrganizationAccessRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LatestOrganizationAccessRequestController extends Controller
{
    public function __invoke(Request $request, OrganizationAccessRequestService $service): JsonResponse
    {
        $payload = $service->latestForUser($request->user());

        return $this->successResponse(
            'Latest organization request retrieved successfully.',
            $payload['organization_access_request']
        );
    }
}
