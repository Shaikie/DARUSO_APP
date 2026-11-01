<?php

namespace App\Models;

use App\Enums\AccountStatus;
use App\Enums\RoleName;
use App\Enums\StudentStatus;
use App\Enums\VerificationStatus;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'account_status',
        'status_reason',
        'status_changed_at',
        'last_login_at',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /* ---------------------------------------------------------------------
     | Relationships
     |---------------------------------------------------------------------*/

    public function studentProfile(): HasOne
    {
        return $this->hasOne(StudentProfile::class);
    }

    public function leaderProfile(): HasOne
    {
        return $this->hasOne(LeaderProfile::class);
    }

    /**
     * @return BelongsToMany<Role, $this>
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    /**
     * Permissions granted through any of the user's roles.
     *
     * @return BelongsToMany<Permission, $this>
     */
    /**
     * Permissions granted through any of the user's roles.
     *
     * Resolved with a subquery rather than a join: a join would emit one row per
     * role/permission pair, which both duplicates rows and makes `exists()`
     * unreliable.
     *
     * @return BelongsToMany<Permission, $this>
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            'role_permissions',
            'role_id',
            'permission_id'
        )->whereIn(
            'role_permissions.role_id',
            $this->roles()->select('roles.id')
        );
    }

    /**
     * @return HasMany<LeaderAssignment, $this>
     */
    public function leaderAssignments(): HasMany
    {
        return $this->hasMany(LeaderAssignment::class);
    }

    /**
     * @return HasMany<CommitteeMember, $this>
     */
    public function committeeMemberships(): HasMany
    {
        return $this->hasMany(CommitteeMember::class);
    }

    /**
     * @return HasMany<Complaint, $this>
     */
    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class, 'creator_id');
    }

    /**
     * Notifications addressed to this user.
     *
     * @return HasMany<Notification, $this>
     */
    public function receivedNotifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'recipient_id');
    }

    /**
     * @return HasMany<Notification, $this>
     */
    public function sentNotifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'sender_id');
    }

    /**
     * @return HasMany<Announcement, $this>
     */
    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class, 'author_id');
    }

    /**
     * @return HasMany<AuditLog, $this>
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'actor_id');
    }

    /**
     * @return HasMany<Feedback, $this>
     */
    public function feedback(): HasMany
    {
        return $this->hasMany(Feedback::class);
    }

    /**
     * @return HasMany<Poll, $this>
     */
    public function pollsCreated(): HasMany
    {
        return $this->hasMany(Poll::class, 'created_by');
    }

    /**
     * @return HasMany<PollVote, $this>
     */
    public function pollVotes(): HasMany
    {
        return $this->hasMany(PollVote::class);
    }

    /**
     * @return HasMany<LeaderTask, $this>
     */
    public function assignedTasks(): HasMany
    {
        return $this->hasMany(LeaderTask::class, 'assigned_to');
    }

    /**
     * @return HasMany<LeaderTask, $this>
     */
    public function createdTasks(): HasMany
    {
        return $this->hasMany(LeaderTask::class, 'assigned_by');
    }

    /**
     * @return HasMany<Handover, $this>
     */
    public function handoversFrom(): HasMany
    {
        return $this->hasMany(Handover::class, 'from_user_id');
    }

    /**
     * @return HasMany<Handover, $this>
     */
    public function handoversTo(): HasMany
    {
        return $this->hasMany(Handover::class, 'to_user_id');
    }

    /**
     * @return HasMany<LeadershipInvitation, $this>
     */
    public function sentInvitations(): HasMany
    {
        return $this->hasMany(LeadershipInvitation::class, 'invited_by');
    }

    /**
     * @return HasMany<MinistryActivityReport, $this>
     */
    public function activityReports(): HasMany
    {
        return $this->hasMany(MinistryActivityReport::class, 'submitted_by');
    }

    /**
     * @return HasMany<NotificationPreference, $this>
     */
    public function notificationPreferences(): HasMany
    {
        return $this->hasMany(NotificationPreference::class);
    }

    /**
     * @return HasMany<PostReport, $this>
     */
    public function postReports(): HasMany
    {
        return $this->hasMany(PostReport::class, 'reporter_id');
    }

    /* ---------------------------------------------------------------------
     | Authorization helpers
     |---------------------------------------------------------------------*/

    public function hasRole(string|RoleName $role): bool
    {
        $name = $role instanceof RoleName ? $role->value : $role;

        if (array_key_exists($name, $this->cachedRoleNames)) {
            return $this->cachedRoleNames[$name];
        }

        $result = $this->roles()->where('name', $name)->exists();

        $this->cachedRoleNames[$name] = $result;

        return $result;
    }

    /**
     * @param  array<int, string|RoleName>  $roles
     */
    public function hasAnyRole(array $roles): bool
    {
        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Whether the user holds a permission through any of their roles.
     *
     * Loads the granted permissions once per request and answers from that set,
     * so a page that asks about several permissions costs a single query rather
     * than one per check.
     */
    public function hasPermission(string $permission): bool
    {
        return $this->grantedPermissions()->contains($permission);
    }

    /**
     * Names of every permission granted to this user.
     *
     * @return Collection<int, string>
     */
    public function grantedPermissions(): Collection
    {
        if ($this->loadedPermissionNames === null) {
            $this->loadedPermissionNames = $this->permissions()
                ->pluck('name')
                ->all();
        }

        return collect($this->loadedPermissionNames);
    }

    /**
     * Discard cached authorization state after roles or permissions change.
     */
    public function forgetAuthorizationCache(): static
    {
        $this->cachedRoleNames = [];
        $this->loadedPermissionNames = null;

        return $this;
    }

    public function isStudent(): bool
    {
        return $this->relationLoaded('studentProfile')
            ? $this->studentProfile !== null
            : $this->studentProfile()->exists();
    }

    public function isLeader(): bool
    {
        if ($this->hasAnyRole([RoleName::Administrator, RoleName::SecretaryGeneral, RoleName::MinistryLeader, RoleName::CommitteeLeader, RoleName::UniversityLeadership])) {
            return true;
        }

        return $this->leaderProfile()->exists();
    }

    public function isAdministrator(): bool
    {
        return $this->hasRole(RoleName::Administrator);
    }

    public function canLogin(): bool
    {
        return $this->account_status->canLogin();
    }

    /**
     * Whether the account has completed administrative approval.
     */
    public function isActiveAccount(): bool
    {
        return $this->account_status->isApproved();
    }

    /**
     * A verified, active student record.
     */
    public function isVerifiedStudent(): bool
    {
        $profile = $this->relationLoaded('studentProfile')
            ? $this->studentProfile
            : $this->studentProfile()->first();

        return $profile !== null
            && $profile->verification_status === VerificationStatus::Verified
            && $profile->status === StudentStatus::Active;
    }

    /**
     * Whether participation features (complaints, polls, feedback) are available.
     */
    public function canParticipate(): bool
    {
        return $this->isVerifiedStudent();
    }

    /**
     * Whether leadership authority is still backed by an active term.
     *
     * Administrators and standing roles are never term-bound. A ministry or
     * committee leader whose recorded assignments and memberships all belong to
     * ended terms loses authority; a leader who has never been scoped to a term
     * keeps it (provisioning) so a role can be granted before an assignment is
     * recorded.
     */
    public function hasActiveLeadershipAuthority(): bool
    {
        if ($this->hasAnyRole([
            RoleName::Administrator,
            RoleName::SecretaryGeneral,
            RoleName::UniversityLeadership,
        ])) {
            return true;
        }

        $termBound = $this->hasAnyRole([RoleName::MinistryLeader, RoleName::CommitteeLeader])
            || $this->leaderProfile()->exists();

        if (! $termBound) {
            return true;
        }

        $hasAssignments = LeaderAssignment::query()->where('user_id', $this->getKey())->exists();
        $hasMemberships = CommitteeMember::query()->where('user_id', $this->getKey())->exists();

        if (! $hasAssignments && ! $hasMemberships) {
            return true;
        }

        return LeaderAssignment::query()
            ->where('user_id', $this->getKey())
            ->whereHas('term', fn (Builder $query) => $query->where('is_active', true))
            ->exists()
            || CommitteeMember::query()
                ->where('user_id', $this->getKey())
                ->whereHas('term', fn (Builder $query) => $query->where('is_active', true))
                ->exists();
    }

    /**
     * Roles that make a user a leader, for display only.
     *
     * @return Collection<int, string>
     */
    public function leadershipRoleLabels(): Collection
    {
        return $this->roles()
            ->whereIn('name', RoleName::leadershipValues())
            ->pluck('name')
            ->map(fn (string $name): string => RoleName::tryFrom($name)?->label() ?? $name);
    }

    public function initials(): string
    {
        return \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($this->name, 0, 1));
    }

    /**
     * Ministries the user currently leads, resolved through the active term.
     *
     * @return Collection<int, int>
     */
    public function ministryIds(): Collection
    {
        return LeaderAssignment::query()
            ->where('user_id', $this->getKey())
            ->whereNotNull('ministry_id')
            ->whereHas('term', fn (Builder $query) => $query->where('is_active', true))
            ->pluck('ministry_id')
            ->unique()
            ->values();
    }

    /**
     * Committees the user currently belongs to, within the active term.
     *
     * @return Collection<int, int>
     */
    public function committeeIds(): Collection
    {
        return CommitteeMember::query()
            ->where('user_id', $this->getKey())
            ->whereHas('term', fn (Builder $query) => $query->where('is_active', true))
            ->pluck('committee_id')
            ->unique()
            ->values();
    }

    public function unreadNotificationCount(): int
    {
        return $this->receivedNotifications()->whereNull('read_at')->count();
    }

    /**
     * Cached role lookups for the current request.
     *
     * @var array<string, bool>
     */
    private array $cachedRoleNames = [];

    /**
     * Permission names granted to this user, or null when not yet loaded.
     *
     * @var array<int, string>|null
     */
    private ?array $loadedPermissionNames = null;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'account_status' => AccountStatus::class,
            'status_changed_at' => 'datetime',
            'last_login_at' => 'datetime',
        ];
    }
}
