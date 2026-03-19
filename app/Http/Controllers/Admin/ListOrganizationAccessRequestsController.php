<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Organization\OrganizationAccessRequestService;
use Illuminate\Http\JsonResponse;

class ListOrganizationAccessRequestsController extends Controller
{
    public function __invoke(OrganizationAccessRequestService $organizationAccessRequestService): JsonResponse
    {
        $payload = $organizationAccessRequestService->listForAdmin();
        $requests = $payload['organization_access_requests'];

        return $this->successResponse(
            'Organization requests retrieved successfully.',
            $requests
        );
    }
}
