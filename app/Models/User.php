<?php

namespace App\Models;

use App\Enums\RoleName;
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
        if ($this->hasRole(RoleName::Administrator) || $this->hasRole(RoleName::SecretaryGeneral)) {
            return true;
        }

        return $this->leaderProfile()->exists();
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
        ];
    }
}
