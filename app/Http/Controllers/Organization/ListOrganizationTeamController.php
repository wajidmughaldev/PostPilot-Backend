<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Services\Organization\OrganizationMemberService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListOrganizationTeamController extends Controller
{
    public function __invoke(Request $request, OrganizationMemberService $service): JsonResponse
    {
        return $this->successResponse(
            'Organization team retrieved successfully.',
            $service->listCurrentTeam($request->user())
        );
    }
}
