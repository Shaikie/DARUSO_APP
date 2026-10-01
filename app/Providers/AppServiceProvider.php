<?php

namespace App\Providers;

use App\Enums\PermissionName;
use App\Models\Announcement;
use App\Models\AuditLog;
use App\Models\Committee;
use App\Models\Complaint;
use App\Models\Document;
use App\Models\Event;
use App\Models\LeaderAssignment;
use App\Models\LeadershipTerm;
use App\Models\Meeting;
use App\Models\Ministry;
use App\Models\Notification;
use App\Models\Post;
use App\Models\Position;
use App\Models\Role;
use App\Models\Setting;
use App\Models\StudentProfile;
use App\Models\User;
use App\Policies\AnnouncementPolicy;
use App\Policies\AuditLogPolicy;
use App\Policies\CommitteePolicy;
use App\Policies\ComplaintPolicy;
use App\Policies\DocumentPolicy;
use App\Policies\EventPolicy;
use App\Policies\LeaderAssignmentPolicy;
use App\Policies\LeadershipTermPolicy;
use App\Policies\MeetingPolicy;
use App\Policies\MinistryPolicy;
use App\Policies\NotificationPolicy;
use App\Policies\PostPolicy;
use App\Policies\PositionPolicy;
use App\Policies\RolePolicy;
use App\Policies\SettingPolicy;
use App\Policies\StudentProfilePolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Model → policy map.
     *
     * Policies are registered explicitly rather than discovered so an
     * unrecognised model cannot silently fall back to "allowed".
     *
     * @var array<class-string, class-string>
     */
    protected array $policies = [
        Announcement::class => AnnouncementPolicy::class,
        AuditLog::class => AuditLogPolicy::class,
        Committee::class => CommitteePolicy::class,
        Complaint::class => ComplaintPolicy::class,
        Document::class => DocumentPolicy::class,
        Event::class => EventPolicy::class,
        LeaderAssignment::class => LeaderAssignmentPolicy::class,
        LeadershipTerm::class => LeadershipTermPolicy::class,
        Meeting::class => MeetingPolicy::class,
        Ministry::class => MinistryPolicy::class,
        Notification::class => NotificationPolicy::class,
        Post::class => PostPolicy::class,
        Position::class => PositionPolicy::class,
        Role::class => RolePolicy::class,
        Setting::class => SettingPolicy::class,
        StudentProfile::class => StudentProfilePolicy::class,
        User::class => UserPolicy::class,
    ];

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }

        $this->registerPermissionGates();
    }

    /**
     * Expose every permission as a Gate ability.
     *
     * `$user->can('complaint.assign')` therefore answers the same question as a
     * policy check, which keeps Blade and controllers consistent.
     */
    private function registerPermissionGates(): void
    {
        foreach (PermissionName::cases() as $permission) {
            Gate::define($permission->value, static fn (User $user): bool => $user->hasPermission($permission->value));
        }

        Gate::define('is-leader', static fn (User $user): bool => $user->isLeader());
        Gate::define('is-student', static fn (User $user): bool => $user->isStudent());
    }
}
