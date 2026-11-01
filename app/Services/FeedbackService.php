<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Enums\FeedbackStatus;
use App\Enums\Priority;
use App\Models\Feedback;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Student suggestions and feedback.
 */
class FeedbackService
{
    public function __construct(
        private readonly AuditLogger $audit,
        private readonly NotificationDispatcher $notifications,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, User $author, ?Request $request = null): Feedback
    {
        $feedback = DB::transaction(function () use ($data, $author, $request): Feedback {
            $feedback = Feedback::create([
                'user_id' => $author->getKey(),
                'subject' => $data['subject'],
                'message' => $data['message'],
                'category' => $data['category'] ?? 'general',
                'status' => FeedbackStatus::Open,
                'is_anonymous' => (bool) ($data['is_anonymous'] ?? false),
            ]);

            $this->audit->log(AuditAction::Created, $feedback, null, ['subject' => $feedback->subject], $request);

            return $feedback;
        });

        return $feedback;
    }

    public function respond(Feedback $feedback, User $responder, string $response, FeedbackStatus $status, ?Request $request = null): Feedback
    {
        $old = $feedback->status->value;

        DB::transaction(function () use ($feedback, $responder, $response, $status, $old, $request): void {
            $feedback->update([
                'response' => $response,
                'responded_by' => $responder->getKey(),
                'responded_at' => now(),
                'status' => $status,
            ]);

            if ($feedback->user !== null) {
                $this->notifications->sendToUser(
                    recipient: $feedback->user,
                    sender: $responder,
                    title: 'Feedback response: '.$feedback->subject,
                    message: $response,
                    priority: Priority::Normal,
                    related: $feedback,
                    actionUrl: route('student.feedback.show', $feedback),
                );
            }

            $this->audit->log(AuditAction::Updated, $feedback, ['status' => $old], [
                'status' => $status->value,
                'response' => $response,
            ], $request);
        });

        return $feedback->fresh(['author', 'responder']);
    }

    public function setStatus(Feedback $feedback, FeedbackStatus $status, User $actor, ?Request $request = null): Feedback
    {
        $old = $feedback->status->value;
        $feedback->update(['status' => $status]);

        $this->audit->log(AuditAction::StatusChanged, $feedback, ['status' => $old], ['status' => $status->value], $request);

        return $feedback->fresh();
    }
}
