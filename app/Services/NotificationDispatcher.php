<?php

namespace App\Services;

use App\Enums\Priority;
use App\Models\AudienceRule;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Creates and manages database notifications.
 *
 * Delivery is separated from business logic on purpose: callers only describe
 * *what* to send and to whom. Channels beyond the in-app database (email, SMS,
 * WhatsApp, push) are added later as additional dispatchers driven by the same
 * command, without touching the services that call this one.
 */
class NotificationDispatcher
{
    public function __construct(
        private readonly AudienceResolver $audience,
    ) {}

    /**
     * Send a notification to one recipient.
     */
    public function sendToUser(
        User $recipient,
        User $sender,
        string $title,
        string $message,
        Priority $priority = Priority::Normal,
        ?Model $related = null,
        ?string $actionUrl = null,
    ): Notification {
        return Notification::create([
            'title' => $title,
            'message' => $message,
            'sender_id' => $sender->getKey(),
            'recipient_id' => $recipient->getKey(),
            'priority' => $priority,
            'related_type' => $related?->getMorphClass(),
            'related_id' => $related?->getKey(),
            'action_url' => $actionUrl,
        ]);
    }

    /**
     * Broadcast to an audience resolved from rules.
     *
     * Each recipient gets their own row so read state is personal, and the rules
     * used are attached to each row for auditability. The caller is expected to
     * have confirmed the audience size (see `AudienceResolver::countRecipients`)
     * when targeting a large group.
     *
     * @param  Collection<int, AudienceRule>  $rules
     * @return int Number of notifications created
     */
    public function broadcast(
        Collection $rules,
        User $sender,
        string $title,
        string $message,
        Priority $priority = Priority::Normal,
        ?Model $related = null,
    ): int {
        $recipientIds = $this->audience->recipientIds($rules);

        if ($recipientIds->isEmpty()) {
            return 0;
        }

        $now = now();
        $relatedType = $related?->getMorphClass();
        $relatedId = $related?->getKey();
        $ruleIds = $rules->pluck('id')->all();

        $rows = [];
        foreach ($recipientIds as $userId) {
            $rows[] = [
                'title' => $title,
                'message' => $message,
                'sender_id' => $sender->getKey(),
                'recipient_id' => $userId,
                'priority' => $priority->value,
                'related_type' => $relatedType,
                'related_id' => $relatedId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        Notification::insert($rows);

        // Record the targeting intent against every delivered notification.
        if ($ruleIds !== []) {
            $delivered = Notification::query()
                ->whereIn('recipient_id', $recipientIds->all())
                ->where('title', $title)
                ->whereNull('read_at')
                ->latest('id')
                ->limit(count($rows))
                ->pluck('id');

            foreach ($delivered as $notificationId) {
                Notification::find($notificationId)?->audienceRules()->sync($ruleIds);
            }
        }

        return count($rows);
    }

    /**
     * Mark one notification read, scoped to its recipient.
     *
     * Ownership is part of the WHERE clause rather than an afterthought, so a
     * guessed id can never mark someone else's notification.
     */
    public function markAsRead(Notification $notification, User $recipient): void
    {
        if ($notification->isRead()) {
            return;
        }

        Notification::query()
            ->whereKey($notification->getKey())
            ->where('recipient_id', $recipient->getKey())
            ->update(['read_at' => now()]);
    }

    /**
     * Mark every unread notification for a recipient.
     *
     * History is preserved: rows are updated, never deleted.
     */
    public function markAllAsRead(User $recipient): int
    {
        return Notification::query()
            ->forRecipient($recipient)
            ->unread()
            ->update(['read_at' => now()]);
    }

    public function unreadCount(User $recipient): int
    {
        return Notification::query()->forRecipient($recipient)->unread()->count();
    }

    /**
     * @return LengthAwarePaginator<int, Notification>
     */
    public function paginateFor(User $recipient, int $perPage = 15, bool $unreadOnly = false): LengthAwarePaginator
    {
        return Notification::query()
            ->with('sender')
            ->forRecipient($recipient)
            ->when($unreadOnly, fn ($query) => $query->unread())
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }
}
