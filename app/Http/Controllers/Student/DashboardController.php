<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Document;
use App\Models\Event;
use App\Models\Meeting;
use App\Services\AudienceResolver;
use App\Services\NotificationDispatcher;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Student dashboard.
 *
 * Only information the student is authorised to see is loaded: announcements,
 * events and meetings are filtered through the audience engine in SQL, and
 * documents through DocumentPolicy's visibility rules.
 */
class DashboardController extends Controller
{
    public function __construct(
        private readonly AudienceResolver $audience,
        private readonly NotificationDispatcher $notifications,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $profile = $user->studentProfile;

        $announcements = $this->audience->constrainSubjectForUser(
            Announcement::query()->with('author')->visibleToAudience()->latest(),
            Announcement::class,
            $user,
        )->limit(5)->get();

        $events = $this->audience->constrainSubjectForUser(
            Event::query()->with('organizer')->upcoming()->limit(5),
            Event::class,
            $user,
        )->get();

        $meetings = $this->audience->constrainSubjectForUser(
            Meeting::query()->with('organizer')->upcoming()->limit(5),
            Meeting::class,
            $user,
        )->get();

        return view('student.dashboard', [
            'user' => $user,
            'profile' => $profile,
            'announcements' => $announcements,
            'events' => $events,
            'meetings' => $meetings,
            'unreadNotifications' => $this->notifications->unreadCount($user),
            'recentNotifications' => $user->receivedNotifications()->with('sender')->latest()->limit(5)->get(),
            'complaintStats' => $user->complaints()
                ->selectRaw('status, count(*) as aggregate')
                ->groupBy('status')
                ->pluck('aggregate', 'status'),
            'openComplaints' => $user->complaints()->open()->count(),
            'documentCount' => Document::query()
                ->whereIn('visibility', ['public', 'students'])
                ->count(),
        ]);
    }
}
