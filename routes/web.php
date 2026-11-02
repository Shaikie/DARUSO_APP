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
use App\Http\Controllers\Leader\PostController as LeaderPostController;
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
use App\Http\Controllers\PostController;
use App\Http\Controllers\PlatformController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Student\RepresentativeController as StudentRepresentativeController;
use App\Http\Controllers\StudentProfileController;
use App\Enums\RoleName;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified', 'account.active'])->group(function (): void {

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

        if ($user->hasRole(RoleName::Administrator)) {
            return redirect()->route('leader.dashboard');
        }

        return redirect()->route('student.posts.index');
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
        // The community feed is available to authenticated users, including leaders.
        // Student-service records remain protected by the active-student gate.
            Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
            Route::get('/posts/{post}', [PostController::class, 'show'])->name('posts.show');
            Route::post('/posts/{post}/like', [PostController::class, 'like'])->name('posts.like');
            Route::post('/posts/{post}/react', [PostController::class, 'react'])->name('posts.react');
            Route::post('/posts/{post}/comments', [PostController::class, 'comment'])->name('posts.comments.store');
            Route::delete('/posts/{post}/comments/{comment}', [PostController::class, 'deleteComment'])->name('posts.comments.destroy');
            Route::post('/posts/{post}/share', [PostController::class, 'share'])->name('posts.share');

        Route::middleware('student.active')->group(function (): void {
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

            Route::get('/feedback', [PlatformController::class, 'feedbackIndex'])->name('feedback.index');
            Route::get('/feedback/create', [PlatformController::class, 'feedbackCreate'])->name('feedback.create');
            Route::post('/feedback', [PlatformController::class, 'feedbackStore'])->name('feedback.store');
            Route::get('/feedback/{feedback}', [PlatformController::class, 'feedbackShow'])->name('feedback.show');
            Route::get('/polls', [PlatformController::class, 'pollsIndex'])->name('polls.index');
            Route::get('/polls/{poll}', [PlatformController::class, 'pollShow'])->name('polls.show');
            Route::post('/polls/{poll}/vote', [PlatformController::class, 'pollVote'])->name('polls.vote');
            Route::get('/calendar', [PlatformController::class, 'calendar'])->name('calendar.index');
            Route::get('/payments', [PaymentController::class, 'studentIndex'])->name('payments.index');
            Route::get('/payments/{paymentRequest}', [PaymentController::class, 'studentShow'])->name('payments.show');
            Route::post('/payments/{paymentRequest}/submit', [PaymentController::class, 'studentSubmit'])->name('payments.submit');

        });

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

    Route::prefix('leader')->name('leader.')->middleware(['leader', 'role.except:student'])->group(function (): void {
        Route::get('/dashboard', [LeaderDashboardController::class, 'index'])->name('dashboard');

        // Communication
        Route::resource('announcements', LeaderAnnouncementController::class);
        Route::resource('posts', LeaderPostController::class)->except(['show']);
        Route::post('posts/{post}/publish', [LeaderPostController::class, 'publish'])->name('posts.publish');
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
        Route::get('payments', [PaymentController::class, 'leaderIndex'])->name('payments.index');
        Route::get('payments/create', [PaymentController::class, 'leaderCreate'])->name('payments.create');
        Route::post('payments', [PaymentController::class, 'leaderStore'])->name('payments.store');
        Route::get('payments/{paymentRequest}', [PaymentController::class, 'leaderShow'])->name('payments.show');
        Route::post('payments/{paymentRequest}/close', [PaymentController::class, 'leaderClose'])->name('payments.close');
        Route::post('payments/{paymentRequest}/remind', [PaymentController::class, 'leaderRemind'])->name('payments.remind');
        Route::post('payments/{paymentRequest}/submissions/{submission}/review', [PaymentController::class, 'leaderReview'])->name('payments.review');

        Route::get('feedback', [PlatformController::class, 'leaderFeedbackIndex'])->name('feedback.index');
        Route::get('feedback/{feedback}', [PlatformController::class, 'leaderFeedbackShow'])->name('feedback.show');
        Route::post('feedback/{feedback}/respond', [PlatformController::class, 'leaderFeedbackRespond'])->name('feedback.respond');

        Route::get('polls', [PlatformController::class, 'leaderPolls'])->name('polls.index');
        Route::get('polls/create', [PlatformController::class, 'pollCreate'])->name('polls.create');
        Route::get('polls/{poll}', [PlatformController::class, 'pollShow'])->name('polls.show');
        Route::get('polls/{poll}/edit', [PlatformController::class, 'pollEdit'])->name('polls.edit');
        Route::put('polls/{poll}', [PlatformController::class, 'pollUpdate'])->name('polls.update');
        Route::post('polls', [PlatformController::class, 'pollStore'])->name('polls.store');
        Route::post('polls/{poll}/open', [PlatformController::class, 'pollOpen'])->name('polls.open');
        Route::post('polls/{poll}/close', [PlatformController::class, 'pollClose'])->name('polls.close');

        Route::get('calendar', [PlatformController::class, 'calendar'])->name('calendar.index');
        Route::post('calendar', [PlatformController::class, 'calendarStore'])->name('calendar.store');
        Route::get('calendar/{entry}/edit', [PlatformController::class, 'calendarEdit'])->name('calendar.edit');
        Route::put('calendar/{entry}', [PlatformController::class, 'calendarUpdate'])->name('calendar.update');
        Route::delete('calendar/{entry}', [PlatformController::class, 'calendarDelete'])->name('calendar.destroy');

        Route::get('tasks', [PlatformController::class, 'tasksIndex'])->name('tasks.index');
        Route::get('tasks/create', [PlatformController::class, 'taskCreate'])->name('tasks.create');
        Route::post('tasks', [PlatformController::class, 'taskStore'])->name('tasks.store');
        Route::get('tasks/{task}', [PlatformController::class, 'taskShow'])->name('tasks.show');
        Route::patch('tasks/{task}/status', [PlatformController::class, 'taskStatus'])->name('tasks.status');

        Route::get('handovers', [PlatformController::class, 'handoversIndex'])->name('handovers.index');
        Route::get('handovers/create', [PlatformController::class, 'handoverCreate'])->name('handovers.create');
        Route::post('handovers', [PlatformController::class, 'handoverStore'])->name('handovers.store');
        Route::get('handovers/{handover}', [PlatformController::class, 'handoverShow'])->name('handovers.show');
        Route::get('handovers/{handover}/edit', [PlatformController::class, 'handoverEdit'])->name('handovers.edit');
        Route::put('handovers/{handover}', [PlatformController::class, 'handoverUpdate'])->name('handovers.update');
        Route::post('handovers/{handover}/submit', [PlatformController::class, 'handoverSubmit'])->name('handovers.submit');
        Route::post('handovers/{handover}/acknowledge', [PlatformController::class, 'handoverAcknowledge'])->name('handovers.acknowledge');

        Route::get('activity-reports', [PlatformController::class, 'activityReportsIndex'])->name('activity-reports.index');
        Route::get('activity-reports/create', [PlatformController::class, 'activityReportCreate'])->name('activity-reports.create');
        Route::post('activity-reports', [PlatformController::class, 'activityReportStore'])->name('activity-reports.store');
        Route::get('activity-reports/{report}', [PlatformController::class, 'activityReportShow'])->name('activity-reports.show');
        Route::get('activity-reports/{report}/edit', [PlatformController::class, 'activityReportEdit'])->name('activity-reports.edit');
        Route::put('activity-reports/{report}', [PlatformController::class, 'activityReportUpdate'])->name('activity-reports.update');
        Route::post('activity-reports/{report}/submit', [PlatformController::class, 'activityReportSubmit'])->name('activity-reports.submit');
        Route::post('activity-reports/{report}/review', [PlatformController::class, 'activityReportReview'])->name('activity-reports.review');

        Route::get('invitations', [PlatformController::class, 'invitationsIndex'])->name('invitations.index');
        Route::get('invitations/create', [PlatformController::class, 'invitationCreate'])->name('invitations.create');
        Route::post('invitations', [PlatformController::class, 'invitationStore'])->name('invitations.store');
        Route::post('invitations/{invitation}/revoke', [PlatformController::class, 'invitationRevoke'])->name('invitations.revoke');

        Route::get('students/directory', [PlatformController::class, 'directoryIndex'])->name('directory.index');
        Route::get('students/directory/{profile}', [PlatformController::class, 'directoryShow'])->name('directory.show');

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

    Route::get('/search', [PlatformController::class, 'search'])->name('search');
    Route::get('/notification-preferences', [PlatformController::class, 'preferences'])->name('notification-preferences.edit');
    Route::put('/notification-preferences', [PlatformController::class, 'updatePreferences'])->name('notification-preferences.update');

    Route::get('/leadership-invitations/{invitation}/accept', [PlatformController::class, 'invitationAccept'])
        ->middleware('signed')->name('leadership.invitations.accept');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/student', [ProfileController::class, 'updateStudentProfile'])->name('profile.student.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
