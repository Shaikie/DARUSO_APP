<?php

namespace App\Services;

use App\Enums\ComplaintStatus;
use App\Enums\Priority;
use App\Models\Complaint;
use App\Models\ComplaintHistory;
use App\Models\Ministry;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * The complaint workflow.
 *
 * All status changes flow through here so that (a) illegal transitions are
 * rejected, (b) a history entry is always written, and (c) the student is
 * notified when their complaint moves. Controllers never mutate status directly.
 */
class ComplaintWorkflow
{
    public function __construct(
        private readonly NotificationDispatcher $notifications,
    ) {}

    /**
     * Move a complaint to a new status, recording history and notifying parties.
     *
     * @throws \DomainException when the transition is not allowed
     */
    public function transitionTo(
        Complaint $complaint,
        ComplaintStatus $target,
        User $actor,
        ?string $notes = null,
        ?string $action = null,
    ): Complaint {
        $current = $complaint->status;

        if ($current === $target) {
            return $complaint;
        }

        if (! $current->canTransitionTo($target)) {
            throw new \DomainException(
                "Cannot transition complaint from [{$current->value}] to [{$target->value}]."
            );
        }

        DB::transaction(function () use ($complaint, $current, $target, $actor, $notes, $action): void {
            $complaint->status = $target;

            if ($target === ComplaintStatus::Resolved) {
                $complaint->resolved_at = now();
            }

            $complaint->save();

            ComplaintHistory::create([
                'complaint_id' => $complaint->getKey(),
                'actor_id' => $actor->getKey(),
                'action' => $action ?? $this->actionFor($target),
                'old_status' => $current->value,
                'new_status' => $target->value,
                'notes' => $notes,
            ]);
        });

        $this->notifyCreator($complaint, $target, $notes);

        return $complaint;
    }

    /**
     * Assign a complaint to a ministry and/or leader.
     */
    public function assign(
        Complaint $complaint,
        User $actor,
        ?Ministry $ministry = null,
        ?User $leader = null,
        ?string $notes = null,
    ): Complaint {
        $old = [
            'assigned_ministry_id' => $complaint->assigned_ministry_id,
            'assigned_leader_id' => $complaint->assigned_leader_id,
        ];

        DB::transaction(function () use ($complaint, $actor, $ministry, $leader, $notes): void {
            if ($ministry !== null) {
                $complaint->assigned_ministry_id = $ministry->getKey();
            }

            if ($leader !== null) {
                $complaint->assigned_leader_id = $leader->getKey();
            }

            $complaint->save();

            ComplaintHistory::create([
                'complaint_id' => $complaint->getKey(),
                'actor_id' => $actor->getKey(),
                'action' => 'assigned',
                'old_status' => $complaint->status->value,
                'new_status' => $complaint->status->value,
                'notes' => $notes,
            ]);
        });

        // Keep the student informed of who is now handling their complaint.
        $this->notifications->sendToUser(
            recipient: $complaint->creator,
            sender: $actor,
            title: 'Complaint assigned: '.$complaint->title,
            message: $notes ?? 'Your complaint has been assigned for action.',
            priority: Priority::Normal,
            related: $complaint,
        );

        return $complaint;
    }

    /**
     * Forward a complaint to a ministry, which moves it into `forwarded`.
     */
    public function forward(Complaint $complaint, Ministry $ministry, User $actor, ?string $notes = null): Complaint
    {
        $this->assign($complaint, $actor, $ministry, null, $notes);

        return $this->transitionTo(
            $complaint,
            ComplaintStatus::Forwarded,
            $actor,
            $notes ?? "Forwarded to {$ministry->name}.",
            'forwarded'
        );
    }

    private function notifyCreator(Complaint $complaint, ComplaintStatus $status, ?string $notes): void
    {
        $this->notifications->sendToUser(
            recipient: $complaint->creator,
            sender: $complaint->assignedLeader ?? $complaint->creator,
            title: 'Complaint update: '.$complaint->title,
            message: $notes ?? ('Status updated to '.$status->label().'.'),
            priority: $status === ComplaintStatus::Resolved
                ? Priority::High
                : Priority::Normal,
            related: $complaint,
        );
    }

    private function actionFor(ComplaintStatus $status): string
    {
        return match ($status) {
            ComplaintStatus::UnderReview => 'under_review',
            ComplaintStatus::Forwarded => 'forwarded',
            ComplaintStatus::InProgress => 'in_progress',
            ComplaintStatus::AwaitingStudent => 'awaiting_student',
            ComplaintStatus::Resolved => 'resolved',
            ComplaintStatus::Closed => 'closed',
            ComplaintStatus::Rejected => 'rejected',
            ComplaintStatus::Submitted => 'submitted',
        };
    }
}
