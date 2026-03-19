<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Organization\OrganizationAccessRequestService;
use Illuminate\Http\JsonResponse;

class ListOrganizationsController extends Controller
{
    public function __invoke(OrganizationAccessRequestService $service): JsonResponse
    {
        $payload = $service->adminOrganizations();
        $organizations = $payload['organizations'];

        return response()->json([
            'success' => true,
            'message' => 'Organizations retrieved successfully.',
            'data' => $organizations,
            'meta' => [
                'total' => count($organizations),
                'per_page' => count($organizations),
                'current_page' => 1,
            ],
        ]);
    }
}
