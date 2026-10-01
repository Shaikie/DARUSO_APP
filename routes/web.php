<?php

use App\Http\Controllers\Leader\AnnouncementController as LeaderAnnouncementController;
use App\Http\Controllers\Leader\AuditLogController as LeaderAuditLogController;
use App\Http\Controllers\Leader\CommitteeController as LeaderCommitteeController;
use App\Http\Controllers\Leader\ComplaintController as LeaderComplaintController;
use App\Http\Controllers\Leader\DashboardController as LeaderDashboardController;
use App\Http\Controllers\Leader\DocumentController as LeaderDocumentController;
use App\Http\Controllers\Leader\EventController as LeaderEventController;
use App\Http\Controllers\Leader\LeadershipController as LeaderLeadershipController;
use App\Http\Controllers\Leader\MeetingController as LeaderMeetingController;
use App\Http\Controllers\Leader\MinistryController as LeaderMinistryController;
use App\Http\Controllers\Leader\NotificationController as LeaderNotificationController;
use App\Http\Controllers\Leader\ReportController as LeaderReportController;
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
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    $user = auth()->user();

    if ($user->isLeader()) {
        return redirect()->route('leader.dashboard');
    }

    return redirect()->route('student.dashboard');
})->middleware(['auth'])->name('dashboard');

Route::middleware(['auth'])->group(function () {
    // Student routes
    Route::prefix('student')->name('student.')->middleware('role:student')->group(function () {
        Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');

        Route::resource('announcements', StudentAnnouncementController::class)->only(['index', 'show']);
        Route::resource('notifications', StudentNotificationController::class)->only(['index', 'show']);
        Route::resource('events', StudentEventController::class)->only(['index', 'show']);
        Route::resource('meetings', StudentMeetingController::class)->only(['index', 'show']);
        Route::resource('complaints', StudentComplaintController::class);
        Route::resource('documents', StudentDocumentController::class)->only(['index', 'show']);

        Route::get('representatives', [StudentRepresentativeController::class, 'index'])->name('representatives.index');
        Route::get('profile', [ProfileController::class, 'edit'])->name('profile');
    });

    // Leader routes
    Route::prefix('leader')->name('leader.')->middleware('role:admin,secretary_general,ministry_leader,committee_leader')->group(function () {
        Route::get('/dashboard', [LeaderDashboardController::class, 'index'])->name('dashboard');

        Route::resource('announcements', LeaderAnnouncementController::class)->except(['show']);
        Route::resource('notifications', LeaderNotificationController::class);
        Route::resource('students', LeaderStudentController::class);
        Route::resource('complaints', LeaderComplaintController::class);
        Route::resource('meetings', LeaderMeetingController::class);
        Route::resource('events', LeaderEventController::class);
        Route::resource('documents', LeaderDocumentController::class);
        Route::resource('ministries', LeaderMinistryController::class);
        Route::resource('committees', LeaderCommitteeController::class);
        Route::resource('leadership', LeaderLeadershipController::class);

        Route::get('reports', [LeaderReportController::class, 'index'])->name('reports.index');
        Route::get('audit-logs', [LeaderAuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('profile', [ProfileController::class, 'edit'])->name('profile');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
