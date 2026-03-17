<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Services\Organization\OrganizationAccessRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MyOrganizationAccessRequestsController extends Controller
{
    public function __invoke(
        Request $request,
        OrganizationAccessRequestService $organizationAccessRequestService
    ): JsonResponse {
        return $this->successResponse(
            'Organization access requests retrieved successfully.',
            $organizationAccessRequestService->listForUser($request->user())
        );
    }
}
