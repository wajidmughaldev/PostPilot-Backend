<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\OrganizationInvite;
use App\Services\Organization\OrganizationMemberService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RevokeOrganizationInviteController extends Controller
{
    public function __invoke(
        Request $request,
        OrganizationInvite $organizationInvite,
        OrganizationMemberService $service
    ): JsonResponse {
        $service->revokeInvite($request->user(), $organizationInvite);

        return $this->successResponse('Organization invite revoked successfully.', null);
    }
}
