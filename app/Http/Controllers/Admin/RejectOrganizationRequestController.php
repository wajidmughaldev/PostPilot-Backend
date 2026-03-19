<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RejectOrganizationRequest;
use App\Models\OrganizationAccessRequest;
use App\Services\Organization\OrganizationAccessRequestService;
use Illuminate\Http\JsonResponse;

class RejectOrganizationRequestController extends Controller
{
    public function __invoke(
        RejectOrganizationRequest $request,
        OrganizationAccessRequest $organizationAccessRequest,
        OrganizationAccessRequestService $service
    ): JsonResponse {
        $payload = $service->reject($request->user(), $organizationAccessRequest, $request->validated('reason'));

        return $this->successResponse(
            'Organization request rejected successfully.',
            $payload['organization_access_request']
        );
    }
}
