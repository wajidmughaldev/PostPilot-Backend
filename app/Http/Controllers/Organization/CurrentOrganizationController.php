<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Services\Organization\OrganizationAccessRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CurrentOrganizationController extends Controller
{
    public function __invoke(Request $request, OrganizationAccessRequestService $service): JsonResponse
    {
        $payload = $service->currentOrganizationForUser($request->user());

        return $this->successResponse(
            'Current organization retrieved successfully.',
            $payload['organization']
        );
    }
}
