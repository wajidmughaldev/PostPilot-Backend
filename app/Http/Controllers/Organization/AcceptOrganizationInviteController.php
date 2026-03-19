<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Http\Requests\Organization\AcceptOrganizationInviteRequest;
use App\Services\Auth\AuthResponseService;
use App\Services\Organization\OrganizationMemberService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AcceptOrganizationInviteController extends Controller
{
    public function __invoke(
        AcceptOrganizationInviteRequest $request,
        string $token,
        OrganizationMemberService $service,
        AuthResponseService $authResponseService
    ): JsonResponse {
        $user = $service->acceptInvite($token, $request->validated());

        Auth::guard('web')->login($user);

        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        return $this->successResponse(
            'Organization invite accepted successfully.',
            $authResponseService->build($user->fresh())
        );
    }
}
