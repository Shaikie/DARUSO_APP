<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Enums\InvitationStatus;
use App\Enums\Priority;
use App\Enums\RoleName;
use App\Mail\LeadershipInvitationMail;
use App\Models\CommitteeMember;
use App\Models\LeaderAssignment;
use App\Models\LeaderProfile;
use App\Models\LeadershipInvitation;
use App\Models\LeadershipTerm;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

/**
 * Issues, accepts, declines and revokes leadership invitations.
 *
 * Security properties:
 *  - only a SHA-256 hash of the token is stored;
 *  - the link is signed and expires;
 *  - an invitation is single-use and the database row is locked while accepted;
 *  - the accepting account's email must match the invited address;
 *  - the inviter cannot grant a role carrying permissions they do not hold.
 */
class LeadershipInvitationService
{
    public const TOKEN_BYTES = 48;

    public function __construct(
        private readonly AuditLogger $audit,
        private readonly NotificationDispatcher $notifications,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @return array{invitation: LeadershipInvitation, token: string}
     */
    public function invite(array $data, User $inviter, ?Request $request = null): array
    {
        $role = RoleName::tryFrom((string) ($data['role_name'] ?? ''));

        if ($role === null || $role === RoleName::Student) {
            throw new \DomainException('Only leadership roles can be invited.');
        }

        $inviteeEmail = mb_strtolower(trim((string) ($data['email'] ?? '')));
        $invitee = User::query()->whereRaw('LOWER(email) = ?', [$inviteeEmail])->first();

        if ($invitee === null || ! $invitee->isStudent() || ! $invitee->isVerifiedStudent() || ! $invitee->isActiveAccount()) {
            throw new \DomainException('Leadership invitations can only be sent to an existing verified, active student account.');
        }

        $this->assertCanGrant($inviter, $role);

        if (! $inviter->isAdministrator() && ! $inviter->hasRole(RoleName::SecretaryGeneral)) {
            $ministryId = $data['ministry_id'] ?? null;

            if ($role !== RoleName::MinistryLeader
                || $ministryId === null
                || ! $inviter->ministryIds()->contains((int) $ministryId)) {
                throw new \DomainException('You may only invite a ministry leader into a ministry you currently lead.');
            }
        }

        $token = Str::random(self::TOKEN_BYTES);
        $expiresAt = now()->addDays((int) ($data['expires_in_days'] ?? 7));

        $invitation = DB::transaction(function () use ($data, $inviter, $role, $token, $expiresAt, $request): LeadershipInvitation {
            $invitation = LeadershipInvitation::create([
                'email' => mb_strtolower(trim((string) $data['email'])),
                'role_name' => $role->value,
                'ministry_id' => $data['ministry_id'] ?? null,
                'committee_id' => $data['committee_id'] ?? null,
                'position_id' => $data['position_id'] ?? null,
                'leadership_term_id' => $data['leadership_term_id'] ?? LeadershipTerm::where('is_active', true)->value('id'),
                'invited_by' => $inviter->getKey(),
                'token_hash' => hash('sha256', $token),
                'status' => InvitationStatus::Pending,
                'expires_at' => $expiresAt,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->audit->log(AuditAction::Invited, $invitation, null, [
                'email' => $invitation->email,
                'role_name' => $invitation->role_name,
                'expires_at' => $invitation->expires_at->toIso8601String(),
            ], $request);

            return $invitation;
        });

        return ['invitation' => $invitation, 'token' => $token];
    }

    public function send(LeadershipInvitation $invitation, string $token): void
    {
        $url = $this->acceptanceUrl($invitation, $token);

        Mail::to($invitation->email)->send(new LeadershipInvitationMail($invitation, $url));
    }

    public function acceptanceUrl(LeadershipInvitation $invitation, string $token): string
    {
        return URL::temporarySignedRoute(
            'leadership.invitations.accept',
            $invitation->expires_at,
            ['invitation' => $invitation->getKey(), 'token' => $token],
        );
    }

    /**
     * @throws \DomainException when the invitation is unusable or the email does not match
     */
    public function accept(LeadershipInvitation $invitation, string $token, User $user, ?Request $request = null): LeadershipInvitation
    {
        return DB::transaction(function () use ($invitation, $token, $user, $request): LeadershipInvitation {
            /** @var LeadershipInvitation $locked */
            $locked = LeadershipInvitation::query()->whereKey($invitation->getKey())->lockForUpdate()->firstOrFail();

            if (! hash_equals($locked->token_hash, hash('sha256', $token))) {
                throw new \DomainException('This invitation link is not valid.');
            }

            if (! $locked->isPending()) {
                throw new \DomainException('This invitation has already been used or revoked.');
            }

            if ($locked->isExpired()) {
                $locked->update(['status' => InvitationStatus::Expired]);
                $this->audit->log(AuditAction::StatusChanged, $locked, ['status' => InvitationStatus::Pending->value], ['status' => InvitationStatus::Expired->value], $request);

                throw new \DomainException('This invitation has expired.');
            }

            if (mb_strtolower($locked->email) !== mb_strtolower($user->email)) {
                throw new \DomainException('This invitation was sent to a different email address.');
            }

            if (! $user->canLogin() || ! $user->isActiveAccount() || ! $user->isVerifiedStudent()) {
                throw new \DomainException('Only an active, verified student account can accept this leadership invitation.');
            }

            $role = Role::firstOrCreate(
                ['name' => $locked->role_name],
                ['description' => RoleName::tryFrom($locked->role_name)?->label() ?? $locked->role_name],
            );

            $studentRoleId = Role::query()->where('name', RoleName::Student->value)->value('id');

            if ($studentRoleId !== null) {
                $user->roles()->detach($studentRoleId);
            }

            $user->roles()->syncWithoutDetaching([$role->getKey()]);
            $user->forgetAuthorizationCache();

            LeaderProfile::firstOrCreate(
                ['user_id' => $user->getKey()],
                ['bio' => $user->name.' serves on the DARUSO leadership team.'],
            );

            $termId = $locked->leadership_term_id ?? LeadershipTerm::where('is_active', true)->value('id');

            if ($locked->position_id !== null && $termId !== null) {
                LeaderAssignment::firstOrCreate(
                    [
                        'user_id' => $user->getKey(),
                        'position_id' => $locked->position_id,
                        'leadership_term_id' => $termId,
                    ],
                    ['ministry_id' => $locked->ministry_id],
                );
            }

            if ($locked->committee_id !== null && $termId !== null) {
                CommitteeMember::firstOrCreate(
                    [
                        'user_id' => $user->getKey(),
                        'committee_id' => $locked->committee_id,
                        'leadership_term_id' => $termId,
                    ],
                    ['role_in_committee' => 'Member'],
                );
            }

            $locked->update([
                'status' => InvitationStatus::Accepted,
                'accepted_at' => now(),
                'accepted_by' => $user->getKey(),
            ]);

            $this->audit->log(AuditAction::Accepted, $locked, ['status' => InvitationStatus::Pending->value], [
                'status' => InvitationStatus::Accepted->value,
                'accepted_by' => $user->getKey(),
            ], $request);

            $this->notifications->sendToUser(
                recipient: $locked->inviter,
                sender: $user,
                title: 'Leadership invitation accepted',
                message: $user->name.' accepted the '.$locked->role_name.' invitation.',
                priority: Priority::Normal,
                related: $locked,
            );

            return $locked->fresh();
        });
    }

    public function decline(LeadershipInvitation $invitation, string $token, User $user, ?Request $request = null): LeadershipInvitation
    {
        return DB::transaction(function () use ($invitation, $token, $user, $request): LeadershipInvitation {
            /** @var LeadershipInvitation $locked */
            $locked = LeadershipInvitation::query()->whereKey($invitation->getKey())->lockForUpdate()->firstOrFail();

            if (! hash_equals($locked->token_hash, hash('sha256', $token)) || ! $locked->isUsable()) {
                throw new \DomainException('This invitation is no longer available.');
            }

            if (mb_strtolower($locked->email) !== mb_strtolower($user->email)) {
                throw new \DomainException('This invitation was sent to a different email address.');
            }

            $locked->update(['status' => InvitationStatus::Declined, 'declined_at' => now()]);

            $this->audit->log(AuditAction::Declined, $locked, ['status' => InvitationStatus::Pending->value], ['status' => InvitationStatus::Declined->value], $request);

            return $locked->fresh();
        });
    }

    public function revoke(LeadershipInvitation $invitation, User $actor, ?Request $request = null): LeadershipInvitation
    {
        if (! $invitation->isPending()) {
            throw new \DomainException('Only a pending invitation can be revoked.');
        }

        $invitation->update(['status' => InvitationStatus::Revoked, 'revoked_at' => now()]);

        $this->audit->log(AuditAction::Revoked, $invitation, ['status' => InvitationStatus::Pending->value], ['status' => InvitationStatus::Revoked->value], $request);

        return $invitation->fresh();
    }

    /**
     * Mark every pending invitation past its expiry as expired.
     */
    public function expireStale(): int
    {
        return LeadershipInvitation::query()
            ->pending()
            ->where('expires_at', '<', now())
            ->update(['status' => InvitationStatus::Expired->value]);
    }

    public function findByToken(string $token): ?LeadershipInvitation
    {
        return LeadershipInvitation::query()
            ->where('token_hash', hash('sha256', $token))
            ->first();
    }

    /**
     * A grantor may not confer a role carrying permissions they do not hold.
     */
    private function assertCanGrant(User $inviter, RoleName $role): void
    {
        if ($inviter->isAdministrator()) {
            return;
        }

        if ($role === RoleName::Administrator) {
            throw new \DomainException('Only an administrator can invite an administrator.');
        }

        $rolePermissionNames = Role::query()
            ->where('name', $role->value)
            ->first()
            ?->permissions()
            ->pluck('name')
            ->all() ?? [];

        $missing = collect($rolePermissionNames)
            ->reject(fn (string $permission): bool => $inviter->hasPermission($permission));

        if ($missing->isNotEmpty()) {
            throw new \DomainException('You cannot invite someone to a role with permissions you do not hold.');
        }
    }
}
