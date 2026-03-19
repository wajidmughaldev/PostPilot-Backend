<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\OrganizationMember;
use App\Services\Organization\OrganizationMemberService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RemoveOrganizationMemberController extends Controller
{
    public function __invoke(
        Request $request,
        OrganizationMember $organizationMember,
        OrganizationMemberService $service
    ): JsonResponse {
        $service->removeMember($request->user(), $organizationMember);

        return $this->successResponse('Organization member removed successfully.', null);
    }
}
