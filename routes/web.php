<?php

use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\Leader\AnnouncementController as LeaderAnnouncementController;
use App\Http\Controllers\Leader\AuditLogController as LeaderAuditLogController;
use App\Http\Controllers\Leader\CommitteeController as LeaderCommitteeController;
use App\Http\Controllers\Leader\ComplaintController as LeaderComplaintController;
use App\Http\Controllers\Leader\DashboardController as LeaderDashboardController;
use App\Http\Controllers\Leader\DocumentController as LeaderDocumentController;
use App\Http\Controllers\Leader\EventController as LeaderEventController;
use App\Http\Controllers\Leader\LeadershipController as LeaderLeadershipController;
use App\Http\Controllers\Leader\LeadershipTermController as LeaderLeadershipTermController;
use App\Http\Controllers\Leader\MeetingController as LeaderMeetingController;
use App\Http\Controllers\Leader\MinistryController as LeaderMinistryController;
use App\Http\Controllers\Leader\NotificationController as LeaderNotificationController;
use App\Http\Controllers\Leader\PositionController as LeaderPositionController;
use App\Http\Controllers\Leader\ReportController as LeaderReportController;
use App\Http\Controllers\Leader\RoleController as LeaderRoleController;
use App\Http\Controllers\Leader\SettingController as LeaderSettingController;
use App\Http\Controllers\Leader\StudentController as LeaderStudentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Student\AnnouncementController as StudentAnnouncementController;
use App\Http\Controllers\Student\ComplaintController as StudentComplaintController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\DocumentController as StudentDocumentController;
use App\Http\Controllers\Student\EventController as StudentEventController;
use App\Http\Controllers\Student\MeetingController as StudentMeetingController;
use App\Http\Controllers\Student\NotificationController as StudentNotificationController;
use App\Http\Controllers\Student\RepresentativeController as StudentRepresentativeController;
use App\Http\Controllers\StudentProfileController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware('auth')->group(function (): void {

    /*
    |--------------------------------------------------------------------------
    | Dashboard routing
    |--------------------------------------------------------------------------
    |
    | One authenticated entry point that redirects to the area appropriate for
    | the user's role, so no view needs to decide its own audience.
    |
    */

    Route::get('/dashboard', function () {
        $user = auth()->user();

        // A fresh registrant has neither a student nor a leader profile, so point
        // them at onboarding instead of a dashboard that expects one.
        if (! $user->isLeader() && ! $user->isStudent()) {
            return view('student.onboarding');
        }

        return $user->isLeader()
            ? redirect()->route('leader.dashboard')
            : redirect()->route('student.dashboard');
    })->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Student area
    |--------------------------------------------------------------------------
    |
    | Read-mostly, plus the complaint submission flow. Individual records are
    | authorized by policy, so the prefix is not a security boundary by itself.
    |
    */

    Route::prefix('student')->name('student.')->group(function (): void {
        Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');

        Route::resource('announcements', StudentAnnouncementController::class)->only(['index', 'show']);
        Route::resource('notifications', StudentNotificationController::class)->only(['index', 'show', 'destroy']);
        Route::patch('notifications/read-all', [StudentNotificationController::class, 'markAllAsRead'])
            ->name('notifications.read-all');
        Route::patch('notifications/{notification}/read', [StudentNotificationController::class, 'markAsRead'])
            ->name('notifications.markAsRead');
        Route::resource('events', StudentEventController::class)->only(['index', 'show']);
        Route::resource('meetings', StudentMeetingController::class)->only(['index', 'show']);
        Route::resource('complaints', StudentComplaintController::class);
        Route::resource('documents', StudentDocumentController::class)->only(['index', 'show']);

        Route::get('/meetings/{meeting}/attachment', [StudentMeetingController::class, 'download'])
            ->name('meetings.download');
        Route::get('/events/{event}/attachment', [StudentEventController::class, 'download'])
            ->name('events.download');
        Route::get('/documents/{document}/download', [StudentDocumentController::class, 'download'])
            ->name('documents.download');

        Route::get('/representatives', [StudentRepresentativeController::class, 'index'])
            ->name('representatives.index');

        // Onboarding for a freshly registered account with no profile yet.
        Route::post('/profile', [StudentProfileController::class, 'store'])
            ->name('profile.store');
    });

    /*
    |--------------------------------------------------------------------------
    | Leader area
    |--------------------------------------------------------------------------
    |
    | Grouped by capability. `role.except:student` keeps leaders out of the
    | student area even if a student role is also attached.
    |
    */

    Route::prefix('leader')->name('leader.')->middleware('role.except:student')->group(function (): void {
        Route::get('/dashboard', [LeaderDashboardController::class, 'index'])->name('dashboard');

        // Communication
        Route::resource('announcements', LeaderAnnouncementController::class);
        Route::post('announcements/{announcement}/submit', [LeaderAnnouncementController::class, 'submitForReview'])
            ->name('announcements.submit');
        Route::post('announcements/{announcement}/publish', [LeaderAnnouncementController::class, 'publish'])
            ->name('announcements.publish');
        Route::post('announcements/{announcement}/archive', [LeaderAnnouncementController::class, 'archive'])
            ->name('announcements.archive');
        Route::post('announcements/{announcement}/revert', [LeaderAnnouncementController::class, 'revertToDraft'])
            ->name('announcements.revert');

        Route::resource('notifications', LeaderNotificationController::class)->except(['destroy']);

        // Organisation
        Route::resource('leadership-terms', LeaderLeadershipTermController::class);
        Route::post('leadership-terms/{leadership_term}/activate', [LeaderLeadershipTermController::class, 'activate'])
            ->name('leadership-terms.activate');

        Route::resource('positions', LeaderPositionController::class);
        Route::resource('ministries', LeaderMinistryController::class);
        Route::resource('committees', LeaderCommitteeController::class);
        Route::post('committees/{committee}/members', [LeaderCommitteeController::class, 'addMember'])
            ->name('committees.members.store');
        Route::delete('committees/{committee}/members/{member}', [LeaderCommitteeController::class, 'removeMember'])
            ->name('committees.members.destroy');

        // The parameter is named explicitly so it matches the controller's
        // `LeaderAssignment $leaderAssignment` implicit binding.
        Route::resource('leadership', LeaderLeadershipController::class)
            ->parameters(['leadership' => 'leader_assignment']);

        // Student services
        Route::resource('students', LeaderStudentController::class);
        Route::resource('complaints', LeaderComplaintController::class)->except(['create', 'store']);
        Route::post('complaints/{complaint}/forward', [LeaderComplaintController::class, 'forward'])
            ->name('complaints.forward');

        // Meetings and events
        Route::resource('meetings', LeaderMeetingController::class);
        Route::resource('events', LeaderEventController::class);

        // Documents
        Route::resource('documents', LeaderDocumentController::class);
        Route::get('documents/{document}/download', [LeaderDocumentController::class, 'download'])
            ->name('documents.download');

        // Administration
        Route::resource('roles', LeaderRoleController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);
        Route::post('roles/{role}/users', [LeaderRoleController::class, 'addUser'])->name('roles.users.store');
        Route::post('users/{user}/roles', [LeaderRoleController::class, 'assign'])->name('roles.assign');
        Route::delete('users/{user}/roles/{role}', [LeaderRoleController::class, 'revoke'])->name('roles.revoke');

        Route::get('reports', [LeaderReportController::class, 'index'])->name('reports.index');
        Route::get('audit-logs', [LeaderAuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('settings', [LeaderSettingController::class, 'index'])->name('settings.index');
        Route::put('settings', [LeaderSettingController::class, 'update'])->name('settings.update');
    });

    /*
    |--------------------------------------------------------------------------
    | Shared attachment downloads
    |--------------------------------------------------------------------------
    */

    Route::get('/attachments/{attachment}/download', [AttachmentController::class, 'download'])
        ->name('attachments.download');

    /*
    |--------------------------------------------------------------------------
    | Profile
    |--------------------------------------------------------------------------
    */

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/student', [ProfileController::class, 'updateStudentProfile'])->name('profile.student.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
