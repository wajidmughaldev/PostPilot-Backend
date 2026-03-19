<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Http\Requests\Organization\StoreOrganizationInviteRequest;
use App\Services\Organization\OrganizationMemberService;
use Illuminate\Http\JsonResponse;

class StoreOrganizationInviteController extends Controller
{
    public function __invoke(StoreOrganizationInviteRequest $request, OrganizationMemberService $service): JsonResponse
    {
        $payload = $service->inviteMember($request->user(), $request->validated());

        return $this->successResponse(
            'Organization invite sent successfully.',
            $payload,
            201
        );
    }
}
