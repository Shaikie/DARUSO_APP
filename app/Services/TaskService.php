<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Enums\Priority;
use App\Enums\TaskStatus;
use App\Models\LeaderTask;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Delegated leadership tasks.
 */
class TaskService
{
    public function __construct(
        private readonly AuditLogger $audit,
        private readonly NotificationDispatcher $notifications,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, User $assigner, ?Request $request = null): LeaderTask
    {
        return DB::transaction(function () use ($data, $assigner, $request): LeaderTask {
            $task = LeaderTask::create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'assigned_to' => $data['assigned_to'],
                'assigned_by' => $assigner->getKey(),
                'ministry_id' => $data['ministry_id'] ?? null,
                'committee_id' => $data['committee_id'] ?? null,
                'status' => TaskStatus::Todo,
                'priority' => $data['priority'] ?? Priority::Normal->value,
                'due_date' => $data['due_date'] ?? null,
            ]);

            if ((int) $task->assigned_to !== $assigner->getKey()) {
                $this->notifications->sendToUser(
                    recipient: $task->assignee,
                    sender: $assigner,
                    title: 'New leadership task: '.$task->title,
                    message: $task->description ?? 'You have been assigned a new task.',
                    priority: Priority::from($task->priority->value),
                    related: $task,
                    actionUrl: route('leader.tasks.show', $task),
                );
            }

            $this->audit->log(AuditAction::Assigned, $task, null, [
                'title' => $task->title,
                'assigned_to' => $task->assigned_to,
            ], $request);

            return $task->load(['assignee', 'assigner', 'ministry', 'committee']);
        });
    }

    public function updateStatus(LeaderTask $task, TaskStatus $status, User $actor, ?Request $request = null): LeaderTask
    {
        $old = $task->status->value;

        $task->update([
            'status' => $status,
            'completed_at' => $status === TaskStatus::Completed ? now() : null,
        ]);

        $this->audit->log(
            $status === TaskStatus::Completed ? AuditAction::Completed : AuditAction::StatusChanged,
            $task,
            ['status' => $old],
            ['status' => $status->value],
            $request,
        );

        return $task->fresh();
    }

    public function delete(LeaderTask $task, User $actor, ?Request $request = null): void
    {
        $this->audit->log(AuditAction::Deleted, $task, $task->only(['title', 'status']), null, $request);

        $task->delete();
    }
}
