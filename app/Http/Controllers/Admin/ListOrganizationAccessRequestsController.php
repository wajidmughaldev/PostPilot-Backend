<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Organization\OrganizationAccessRequestService;
use Illuminate\Http\JsonResponse;

class ListOrganizationAccessRequestsController extends Controller
{
    public function __invoke(OrganizationAccessRequestService $organizationAccessRequestService): JsonResponse
    {
        return $this->successResponse(
            'Organization access requests retrieved successfully.',
            $organizationAccessRequestService->listForAdmin()
        );
    }
}
