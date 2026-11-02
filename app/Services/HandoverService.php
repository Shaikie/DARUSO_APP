<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Enums\HandoverStatus;
use App\Enums\Priority;
use App\Models\Handover;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Leadership handover records.
 */
class HandoverService
{
    public function __construct(
        private readonly AuditLogger $audit,
        private readonly NotificationDispatcher $notifications,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, User $author, ?Request $request = null): Handover
    {
        return DB::transaction(function () use ($data, $author, $request): Handover {
            $handover = Handover::create([
                'title' => $data['title'],
                'summary' => $data['summary'],
                'outstanding_items' => $data['outstanding_items'] ?? null,
                'recommendations' => $data['recommendations'] ?? null,
                'from_user_id' => $author->getKey(),
                'to_user_id' => $data['to_user_id'],
                'ministry_id' => $data['ministry_id'] ?? null,
                'committee_id' => $data['committee_id'] ?? null,
                'leadership_term_id' => $data['leadership_term_id'] ?? null,
                'status' => HandoverStatus::Draft,
            ]);

            $this->audit->log(AuditAction::Created, $handover, null, ['title' => $handover->title], $request);

            return $handover->load(['fromUser', 'toUser']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Handover $handover, array $data, User $actor, ?Request $request = null): Handover
    {
        if ($handover->status !== HandoverStatus::Draft) {
            throw new \DomainException('Only a draft handover can be edited.');
        }

        $old = $handover->only(['title', 'summary']);

        $handover->update($data);

        $this->audit->log(AuditAction::Updated, $handover, $old, $handover->only(['title', 'summary']), $request);

        return $handover->fresh();
    }

    public function submit(Handover $handover, User $actor, ?Request $request = null): Handover
    {
        if ($handover->status !== HandoverStatus::Draft) {
            throw new \DomainException('This handover has already been submitted.');
        }

        $handover->update([
            'status' => HandoverStatus::Submitted,
            'submitted_at' => now(),
        ]);

        $this->notifications->sendToUser(
            recipient: $handover->toUser,
            sender: $actor,
            title: 'Leadership handover: '.$handover->title,
            message: 'A handover record has been submitted for your attention.',
            priority: Priority::Normal,
            related: $handover,
            actionUrl: route('leader.handovers.show', $handover),
        );

        $this->audit->log(AuditAction::Submitted, $handover, ['status' => HandoverStatus::Draft->value], ['status' => HandoverStatus::Submitted->value], $request);

        return $handover->fresh();
    }

    public function acknowledge(Handover $handover, User $actor, ?Request $request = null): Handover
    {
        if ($handover->to_user_id !== $actor->getKey()) {
            throw new \DomainException('Only the incoming officer can acknowledge this handover.');
        }

        if ($handover->status !== HandoverStatus::Submitted) {
            throw new \DomainException('This handover is not awaiting acknowledgement.');
        }

        $handover->update([
            'status' => HandoverStatus::Acknowledged,
            'acknowledged_at' => now(),
        ]);

        $this->notifications->sendToUser(
            recipient: $handover->fromUser,
            sender: $actor,
            title: 'Handover acknowledged: '.$handover->title,
            message: $actor->name.' acknowledged your handover record.',
            priority: Priority::Normal,
            related: $handover,
            actionUrl: route('leader.handovers.show', $handover),
        );

        $this->audit->log(AuditAction::Acknowledged, $handover, ['status' => HandoverStatus::Submitted->value], ['status' => HandoverStatus::Acknowledged->value], $request);

        return $handover->fresh();
    }
}
