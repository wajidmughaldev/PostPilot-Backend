<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateOrganizationFromRequest;
use App\Services\Organization\OrganizationProvisioningService;
use Illuminate\Http\JsonResponse;

class CreateOrganizationController extends Controller
{
    public function __invoke(
        CreateOrganizationFromRequest $request,
        OrganizationProvisioningService $organizationProvisioningService
    ): JsonResponse {
        return $this->successResponse(
            'Organization created successfully.',
            $organizationProvisioningService->createFromAccessRequest($request->user(), $request->validated()),
            201
        );
    }
}
