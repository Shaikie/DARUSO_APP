<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Services\NotificationDispatcher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The reader's own notification inbox.
 *
 * Queries are always scoped by recipient, and marking as read goes through
 * NotificationDispatcher, which adds the recipient to the WHERE clause.
 */
class NotificationController extends Controller
{
    public function __construct(
        private readonly NotificationDispatcher $notifications,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        return view('student.notifications.index', [
            'notifications' => $this->notifications->paginateFor($user, 15, $request->boolean('unread')),
            'unreadCount' => $this->notifications->unreadCount($user),
        ]);
    }

    public function show(Request $request, Notification $notification): View
    {
        $this->authorize('view', $notification);

        // Opening a notification marks it read, for the recipient only.
        if ($notification->recipient_id === $request->user()->getKey()) {
            $this->notifications->markAsRead($notification, $request->user());
        }

        return view('student.notifications.show', [
            'notification' => $notification->load(['sender', 'related']),
        ]);
    }

    /**
     * Mark one notification read. PATCH rather than POST because read state is
     * an update to an existing record, not a state-changing command.
     */
    public function markAsRead(Request $request, Notification $notification): RedirectResponse
    {
        $this->authorize('markAsRead', $notification);

        $this->notifications->markAsRead($notification, $request->user());

        return back()->with('success', 'Notification marked as read.');
    }

    /**
     * Mark every unread notification for the signed-in recipient as read.
     */
    public function markAllAsRead(Request $request): RedirectResponse
    {
        $count = $this->notifications->markAllAsRead($request->user());

        return back()->with('success', "{$count} notification(s) marked as read.");
    }

    /**
     * Notifications are never deleted; read state is preserved instead.
     */
    public function destroy(Request $request, Notification $notification): RedirectResponse
    {
        abort(403, 'Notifications cannot be deleted.');
    }
}
