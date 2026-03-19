<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\OrganizationAccessRequest;
use App\Services\Organization\OrganizationAccessRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WithdrawOrganizationAccessRequestController extends Controller
{
    public function __invoke(
        Request $request,
        OrganizationAccessRequest $organizationAccessRequest,
        OrganizationAccessRequestService $service
    ): JsonResponse {
        $service->withdraw($request->user(), $organizationAccessRequest);

        return response()->json([
            'success' => true,
            'message' => 'Organization request withdrawn successfully.',
            'data' => null,
        ]);
    }
}
