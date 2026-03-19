<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrganizationAccessRequest;
use App\Services\Organization\OrganizationAccessRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApproveOrganizationRequestController extends Controller
{
    public function __invoke(
        Request $request,
        OrganizationAccessRequest $organizationAccessRequest,
        OrganizationAccessRequestService $service
    ): JsonResponse {
        $payload = $service->approve($request->user(), $organizationAccessRequest);

        return $this->successResponse(
            'Organization request approved successfully.',
            $payload['organization_access_request']
        );
    }
}
