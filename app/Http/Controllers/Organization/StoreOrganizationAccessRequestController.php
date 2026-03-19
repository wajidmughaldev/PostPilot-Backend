<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Http\Requests\Organization\StoreOrganizationAccessRequest;
use App\Services\Organization\OrganizationAccessRequestService;
use Illuminate\Http\JsonResponse;

class StoreOrganizationAccessRequestController extends Controller
{
    public function __invoke(
        StoreOrganizationAccessRequest $request,
        OrganizationAccessRequestService $organizationAccessRequestService
    ): JsonResponse {
        $payload = $organizationAccessRequestService->submit($request->user(), $request->validated());

        return $this->successResponse(
            'Organization request submitted successfully.',
            $payload['organization_access_request'],
            201
        );
    }
}
