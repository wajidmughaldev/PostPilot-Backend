<?php

namespace App\Services\Organization;

use App\Http\Resources\OrganizationInviteResource;
use App\Http\Resources\OrganizationMemberResource;
use App\Models\OrganizationInvite;
use App\Models\OrganizationMember;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrganizationMemberService
{
    public function listCurrentTeam(User $user): array
    {
        $membership = $this->resolveActiveMembership($user);

        $organization = $membership->organization()->firstOrFail();

        $members = OrganizationMemberResource::collection(
            $organization->members()
                ->with('user')
                ->orderByRaw("case when role = 'owner' then 0 when role = 'manager' then 1 else 2 end")
                ->orderBy('created_at')
                ->get()
        )->resolve();

        $invites = OrganizationInviteResource::collection(
            $organization->invites()
                ->with('inviter')
                ->where('status', 'pending')
                ->latest()
                ->get()
        )->resolve();

        return [
            'members' => $members,
            'invites' => $invites,
        ];
    }

    public function inviteMember(User $user, array $data): array
    {
        $membership = $this->resolveOwnerMembership($user);
        $organization = $membership->organization()->firstOrFail();

        $email = strtolower(trim($data['email']));

        $existingMember = $organization->members()
            ->whereHas('user', fn ($query) => $query->where('email', $email))
            ->where('status', 'active')
            ->exists();

        if ($existingMember) {
            throw ValidationException::withMessages([
                'email' => ['That user is already an active member of this organization.'],
            ]);
        }

        $pendingInvite = $organization->invites()
            ->where('email', $email)
            ->where('status', 'pending')
            ->exists();

        if ($pendingInvite) {
            throw ValidationException::withMessages([
                'email' => ['A pending invite already exists for this email address.'],
            ]);
        }

        $invite = OrganizationInvite::query()->create([
            'organization_id' => $organization->id,
            'email' => $email,
            'role' => $data['role'],
            'token' => Str::random(64),
            'status' => 'pending',
            'invited_by' => $user->id,
            'expires_at' => now()->addDays(7),
        ]);

        return [
            'invite' => OrganizationInviteResource::make($invite->load('inviter'))->resolve(),
        ];
    }

    public function updateMemberRole(User $user, OrganizationMember $targetMember, string $role): array
    {
        $membership = $this->resolveOwnerMembership($user);
        $this->assertSameOrganization($membership, $targetMember->organization_id);

        if ((int) $targetMember->user_id === (int) $user->id) {
            throw ValidationException::withMessages([
                'member' => ['You cannot change your own organization role.'],
            ]);
        }

        if ($targetMember->role === 'owner') {
            throw ValidationException::withMessages([
                'member' => ['Organization owner role cannot be changed here.'],
            ]);
        }

        $targetMember->update([
            'role' => $role,
        ]);

        return [
            'member' => OrganizationMemberResource::make($targetMember->fresh('user'))->resolve(),
        ];
    }

    public function removeMember(User $user, OrganizationMember $targetMember): void
    {
        $membership = $this->resolveOwnerMembership($user);
        $this->assertSameOrganization($membership, $targetMember->organization_id);

        if ((int) $targetMember->user_id === (int) $user->id) {
            throw ValidationException::withMessages([
                'member' => ['You cannot remove yourself from the organization.'],
            ]);
        }

        if ($targetMember->role === 'owner') {
            throw ValidationException::withMessages([
                'member' => ['Organization owner cannot be removed here.'],
            ]);
        }

        $targetMember->delete();
    }

    public function revokeInvite(User $user, OrganizationInvite $invite): void
    {
        $membership = $this->resolveOwnerMembership($user);
        $this->assertSameOrganization($membership, $invite->organization_id);

        if ($invite->status !== 'pending') {
            throw ValidationException::withMessages([
                'invite' => ['Only pending invites can be revoked.'],
            ]);
        }

        $invite->delete();
    }

    public function previewInvite(string $token): array
    {
        $invite = $this->findPendingInviteByToken($token);

        return [
            'token' => $invite->token,
            'organizationName' => $invite->organization?->name,
            'invitedEmail' => $invite->email,
            'role' => $invite->role,
            'invitedByName' => $invite->inviter?->name,
        ];
    }

    public function acceptInvite(string $token, array $data): User
    {
        $invite = $this->findPendingInviteByToken($token);

        return DB::transaction(function () use ($invite, $data): User {
            $user = User::query()->where('email', $invite->email)->first();

            if ($user) {
                if (! Hash::check($data['password'], $user->password)) {
                    throw ValidationException::withMessages([
                        'password' => ['Use your existing account password to accept this invite.'],
                    ]);
                }
            } else {
                $user = User::query()->create([
                    'name' => $data['name'],
                    'email' => $invite->email,
                    'password' => $data['password'],
                    'platform_role' => 'user',
                ]);
            }

            $existingMembership = OrganizationMember::query()
                ->where('organization_id', $invite->organization_id)
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->exists();

            if ($existingMembership) {
                throw ValidationException::withMessages([
                    'invite' => ['This user is already part of the organization.'],
                ]);
            }

            OrganizationMember::query()->create([
                'organization_id' => $invite->organization_id,
                'user_id' => $user->id,
                'role' => $invite->role,
                'status' => 'active',
                'created_by' => $invite->invited_by,
            ]);

            $invite->update([
                'status' => 'accepted',
                'accepted_by' => $user->id,
                'accepted_at' => now(),
            ]);

            return $user;
        });
    }

    protected function resolveActiveMembership(User $user): OrganizationMember
    {
        $membership = $user->organizationMembers()
            ->where('status', 'active')
            ->with('organization')
            ->latest()
            ->first();

        if (! $membership) {
            throw new AuthorizationException('You are not part of an active organization.');
        }

        return $membership;
    }

    protected function resolveOwnerMembership(User $user): OrganizationMember
    {
        $membership = $this->resolveActiveMembership($user);

        if ($membership->role !== 'owner') {
            throw new AuthorizationException('Only the organization owner can manage members.');
        }

        return $membership;
    }

    protected function assertSameOrganization(OrganizationMember $membership, int|string $organizationId): void
    {
        if ((int) $membership->organization_id !== (int) $organizationId) {
            throw new AuthorizationException('You can only manage members in your own organization.');
        }
    }

    protected function findPendingInviteByToken(string $token): OrganizationInvite
    {
        $invite = OrganizationInvite::query()
            ->with(['organization', 'inviter'])
            ->where('token', $token)
            ->where('status', 'pending')
            ->first();

        if (! $invite || ($invite->expires_at && $invite->expires_at->isPast())) {
            throw ValidationException::withMessages([
                'invite' => ['This invite link is invalid or expired.'],
            ]);
        }

        return $invite;
    }
}
