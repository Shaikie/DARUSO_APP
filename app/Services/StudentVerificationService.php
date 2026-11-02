<?php

namespace App\Services;

use App\Enums\AccountStatus;
use App\Enums\AuditAction;
use App\Enums\Priority;
use App\Enums\RoleName;
use App\Enums\StudentStatus;
use App\Enums\VerificationStatus;
use App\Models\Role;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Owns the student verification lifecycle.
 *
 * Registration only proves control of an email address. A student record stays
 * `pending` until a leader holding `student.verify` reviews it. Every transition
 * writes a history row and an audit entry inside the same transaction.
 */
class StudentVerificationService
{
    public function __construct(
        private readonly AuditLogger $audit,
        private readonly NotificationDispatcher $notifications,
    ) {}

    /**
     * Create or re-submit the signed-in user's academic record.
     *
     * Re-submission resets a rejected record to pending but never grants the
     * Student role or Active standing by itself.
     *
     * @param  array<string, mixed>  $academic
     */
    public function submit(User $user, array $academic, ?Request $request = null): StudentProfile
    {
        $profile = DB::transaction(function () use ($user, $academic, $request): StudentProfile {
            $profile = $user->studentProfile()->firstOrNew();
            $oldStatus = $profile->exists ? $profile->verification_status?->value : null;

            $profile->fill($academic);
            $profile->status = StudentStatus::PendingVerification;
            $profile->verification_status = VerificationStatus::Pending;
            $profile->submitted_at = now();
            $profile->verified_at = null;
            $profile->verified_by = null;
            $profile->rejection_reason = null;
            $profile->save();

            $this->record($profile, $user, 'submitted', $oldStatus, VerificationStatus::Pending->value, 'Student submitted their record for verification.');

            $this->audit->log(AuditAction::Submitted, $profile, null, [
                'registration_number' => $profile->registration_number,
                'verification_status' => VerificationStatus::Pending->value,
            ], $request);

            return $profile;
        });

        return $profile->fresh(['user']);
    }

    public function verify(StudentProfile $profile, User $actor, ?string $notes = null, ?Request $request = null): StudentProfile
    {
        return $this->transition(
            $profile,
            $actor,
            VerificationStatus::Verified,
            StudentStatus::Active,
            $notes,
            $request,
        );
    }

    public function reject(StudentProfile $profile, User $actor, string $reason, ?Request $request = null): StudentProfile
    {
        $profile = $this->transition(
            $profile,
            $actor,
            VerificationStatus::Rejected,
            StudentStatus::Rejected,
            $reason,
            $request,
        );

        $profile->update(['rejection_reason' => $reason]);

        return $profile->fresh();
    }

    public function suspend(StudentProfile $profile, User $actor, string $reason, ?Request $request = null): StudentProfile
    {
        return $this->transition(
            $profile,
            $actor,
            $profile->verification_status,
            StudentStatus::Suspended,
            $reason,
            $request,
        );
    }

    public function reactivate(StudentProfile $profile, User $actor, ?string $notes = null, ?Request $request = null): StudentProfile
    {
        return $this->transition(
            $profile,
            $actor,
            VerificationStatus::Verified,
            StudentStatus::Active,
            $notes,
            $request,
        );
    }

    /**
     * Change the account standing (pending/active/suspended/rejected).
     */
    public function setAccountStatus(User $target, AccountStatus $status, User $actor, ?string $reason = null, ?Request $request = null): User
    {
        $old = $target->account_status ?? AccountStatus::Active;

        if ($old === $status) {
            return $target;
        }

        DB::transaction(function () use ($target, $status, $reason): void {
            $target->update([
                'account_status' => $status,
                'status_reason' => $reason,
                'status_changed_at' => now(),
            ]);

            if (! $status->canLogin() && config('session.driver') === 'database') {
                DB::table(config('session.table', 'sessions'))
                    ->where('user_id', $target->getKey())
                    ->delete();
            }
        });

        $this->audit->log(
            match ($status) {
                AccountStatus::Suspended => AuditAction::Suspended,
                AccountStatus::Rejected => AuditAction::Rejected,
                AccountStatus::Active => AuditAction::Reactivated,
                AccountStatus::Pending => AuditAction::StatusChanged,
            },
            $target,
            ['account_status' => $old->value],
            ['account_status' => $status->value, 'reason' => $reason],
            $request,
        );

        return $target->fresh();
    }

    private function transition(
        StudentProfile $profile,
        User $actor,
        VerificationStatus $verification,
        StudentStatus $studentStatus,
        ?string $notes,
        ?Request $request,
    ): StudentProfile {
        $oldVerification = $profile->verification_status->value;
        $oldStudent = $profile->status->value;

        DB::transaction(function () use ($profile, $actor, $verification, $studentStatus, $notes, $oldVerification, $oldStudent, $request): void {
            $profile->update([
                'verification_status' => $verification,
                'status' => $studentStatus,
                'verified_at' => $verification === VerificationStatus::Verified ? now() : null,
                'verified_by' => $verification === VerificationStatus::Verified ? $actor->getKey() : $profile->verified_by,
                'rejection_reason' => $verification === VerificationStatus::Rejected ? $notes : null,
            ]);

            if ($verification === VerificationStatus::Verified) {
                $this->ensureStudentRole($profile->user);

                $this->notifications->sendToUser(
                    recipient: $profile->user,
                    sender: $actor,
                    title: 'Student record verified',
                    message: $notes ?? 'Your student record has been verified. You now have full access to student features.',
                    priority: Priority::Normal,
                    related: $profile,
                    actionUrl: route('student.dashboard'),
                );
            }

            if ($verification === VerificationStatus::Rejected) {
                $this->notifications->sendToUser(
                    recipient: $profile->user,
                    sender: $actor,
                    title: 'Student record requires attention',
                    message: $notes ?? 'Your student record could not be verified. Please update your details and resubmit.',
                    priority: Priority::High,
                    related: $profile,
                    actionUrl: route('profile.edit'),
                );
            }

            $this->record(
                $profile,
                $actor,
                $verification->value,
                $oldVerification.'/'.$oldStudent,
                $verification->value.'/'.$studentStatus->value,
                $notes,
            );

            $this->audit->log(
                $verification === VerificationStatus::Verified ? AuditAction::Verified
                    : ($verification === VerificationStatus::Rejected ? AuditAction::Rejected : AuditAction::StatusChanged),
                $profile,
                ['verification_status' => $oldVerification, 'status' => $oldStudent],
                ['verification_status' => $verification->value, 'status' => $studentStatus->value, 'notes' => $notes],
                $request,
            );
        });

        return $profile->fresh(['user']);
    }

    private function ensureStudentRole(User $user): void
    {
        $role = Role::firstOrCreate(
            ['name' => RoleName::Student->value],
            ['description' => RoleName::Student->label()],
        );

        $user->roles()->syncWithoutDetaching([$role->getKey()]);
        $user->forgetAuthorizationCache();
    }

    private function record(
        StudentProfile $profile,
        ?User $actor,
        string $action,
        ?string $oldStatus,
        ?string $newStatus,
        ?string $notes,
    ): void {
        $profile->verificationHistory()->create([
            'actor_id' => $actor?->getKey(),
            'action' => $action,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'notes' => $notes,
            'created_at' => now(),
        ]);
    }
}
