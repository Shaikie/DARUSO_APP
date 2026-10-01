<?php

namespace App\Http\Controllers\Leader;

use App\Enums\PermissionName;
use App\Enums\Priority;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNotificationRequest;
use App\Models\Notification;
use App\Models\User;
use App\Services\AttachmentService;
use App\Services\AudienceResolver;
use App\Services\AudienceRuleRepository;
use App\Services\AuditLogger;
use App\Services\NotificationDispatcher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Leader-facing notification management.
 *
 * This controller manages messages a leader sends. The recipient's own inbox
 * (including mark-as-read) is handled by the Student/NotificationController,
 * which is scoped by policy to the recipient.
 */
class NotificationController extends Controller
{
    public function __construct(
        private readonly NotificationDispatcher $dispatcher,
        private readonly AudienceRuleRepository $audienceRules,
        private readonly AudienceResolver $audience,
        private readonly AuditLogger $audit,
        private readonly AttachmentService $attachments,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Notification::class);

        $notifications = Notification::query()
            ->with(['recipient', 'sender', 'related'])
            ->where('sender_id', $request->user()->getKey())
            ->when($request->filled('priority'), fn ($query) => $query->where('priority', $request->string('priority')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('leader.notifications.index', [
            'notifications' => $notifications,
            'stats' => [
                'total' => Notification::where('sender_id', $request->user()->getKey())->count(),
                'read' => Notification::where('sender_id', $request->user()->getKey())->whereNotNull('read_at')->count(),
            ],
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Notification::class);

        return view('leader.notifications.create', [
            'audienceOptions' => $this->audienceRules->options(),
            'recipients' => User::query()
                ->whereHas('studentProfile')
                ->with('studentProfile')
                ->orderBy('name')
                ->limit(500)
                ->get()
                ->map(fn (User $user): array => [
                    'id' => $user->getKey(),
                    'name' => $user->name.' ('.$user->email.')',
                ]),
        ]);
    }

    public function store(StoreNotificationRequest $request): RedirectResponse
    {
        $this->authorize('create', Notification::class);

        // Creating and sending are separate permissions: composing a message is not
        // the same authority as delivering it to students.
        if (! $request->user()->hasPermission(PermissionName::NotificationSend->value)) {
            abort(403, 'You do not have permission to send notifications.');
        }

        $priority = Priority::from($request->string('priority')->value);
        $created = 0;

        // Individual recipients.
        foreach ($request->validated('recipients', []) as $recipientId) {
            $recipient = User::find($recipientId);

            if ($recipient === null || $recipient->is($request->user())) {
                continue;
            }

            $this->dispatcher->sendToUser(
                recipient: $recipient,
                sender: $request->user(),
                title: $request->string('title')->value,
                message: $request->string('message')->value,
                priority: $priority,
            );

            $created++;
        }

        // Broadcast targeting.
        $rules = $this->audienceRules->persist($this->audienceRules->build($request->audiencePayload()));

        if ($rules->isNotEmpty()) {
            $limit = (int) config('daruso.audience.notification_materialisation_limit');
            $recipientCount = $this->audience->countRecipients($rules);

            if ($recipientCount > $limit) {
                return back()->with(
                    'error',
                    "That audience resolves to {$recipientCount} recipients, above the {$limit} limit for direct notifications. "
                    .'Publish an announcement instead — it stays a single targeted record.'
                );
            }

            $created += $this->dispatcher->broadcast(
                rules: $rules,
                sender: $request->user(),
                title: $request->string('title')->value,
                message: $request->string('message')->value,
                priority: $priority,
            );
        }

        $this->audit->log('notification_sent', null, null, [
            'recipients' => $created,
            'title' => $request->string('title')->value,
        ], $request);

        return redirect()
            ->route('leader.notifications.index')
            ->with('success', "{$created} notification(s) sent.");
    }

    public function show(Notification $notification): View
    {
        $this->authorize('view', $notification);

        return view('leader.notifications.show', [
            'notification' => $notification->load(['recipient', 'sender', 'related', 'audienceRules']),
        ]);
    }

    public function edit(Request $request, Notification $notification): View
    {
        $this->authorize('view', $notification);

        if ($notification->sender_id !== $request->user()->getKey()) {
            abort(403, 'You can only edit notifications you sent.');
        }

        return view('leader.notifications.edit', [
            'notification' => $notification,
            'audienceOptions' => $this->audienceRules->options(),
        ]);
    }

    /**
     * Editing a delivered notification's text is not permitted: recipients may
     * already have acted on the original wording.
     */
    public function update(Request $request, Notification $notification): RedirectResponse
    {
        $this->authorize('view', $notification);

        return back()->with('error', 'Sent notifications cannot be edited.');
    }

    /**
     * Notifications are never deleted so history is preserved.
     */
    public function destroy(Request $request, Notification $notification): RedirectResponse
    {
        abort(403, 'Notifications cannot be deleted; read state is preserved instead.');
    }
}
