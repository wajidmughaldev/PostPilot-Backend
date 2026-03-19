<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Http\Requests\Organization\UpdateOrganizationMemberRequest;
use App\Models\OrganizationMember;
use App\Services\Organization\OrganizationMemberService;
use Illuminate\Http\JsonResponse;

class UpdateOrganizationMemberController extends Controller
{
    public function __invoke(
        UpdateOrganizationMemberRequest $request,
        OrganizationMember $organizationMember,
        OrganizationMemberService $service
    ): JsonResponse {
        $payload = $service->updateMemberRole(
            $request->user(),
            $organizationMember,
            $request->validated()['role']
        );

        return $this->successResponse('Organization member updated successfully.', $payload);
    }
}
