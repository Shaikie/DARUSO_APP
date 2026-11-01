<?php

namespace App\Services;

use App\Enums\ActivityReportStatus;
use App\Enums\AuditAction;
use App\Enums\Priority;
use App\Models\MinistryActivityReport;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Ministry activity reports.
 */
class ActivityReportService
{
    public function __construct(
        private readonly AuditLogger $audit,
        private readonly NotificationDispatcher $notifications,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, User $author, ?Request $request = null): MinistryActivityReport
    {
        return DB::transaction(function () use ($data, $author, $request): MinistryActivityReport {
            $report = MinistryActivityReport::create([
                'ministry_id' => $data['ministry_id'],
                'leadership_term_id' => $data['leadership_term_id'] ?? null,
                'submitted_by' => $author->getKey(),
                'period_start' => $data['period_start'],
                'period_end' => $data['period_end'],
                'summary' => $data['summary'],
                'achievements' => $data['achievements'] ?? null,
                'challenges' => $data['challenges'] ?? null,
                'recommendations' => $data['recommendations'] ?? null,
                'status' => ActivityReportStatus::Draft,
            ]);

            $this->audit->log(AuditAction::Created, $report, null, ['ministry_id' => $report->ministry_id], $request);

            return $report->load(['ministry', 'term']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(MinistryActivityReport $report, array $data, User $actor, ?Request $request = null): MinistryActivityReport
    {
        if ($report->status === ActivityReportStatus::Reviewed) {
            throw new \DomainException('A reviewed report cannot be edited.');
        }

        $old = $report->only(['summary', 'period_start', 'period_end']);

        $report->update($data);

        $this->audit->log(AuditAction::Updated, $report, $old, $report->only(['summary', 'period_start', 'period_end']), $request);

        return $report->fresh();
    }

    public function submit(MinistryActivityReport $report, User $actor, ?Request $request = null): MinistryActivityReport
    {
        if ($report->status === ActivityReportStatus::Reviewed) {
            throw new \DomainException('This report has already been reviewed.');
        }

        $report->update([
            'status' => ActivityReportStatus::Submitted,
            'submitted_at' => now(),
        ]);

        $this->audit->log(AuditAction::Submitted, $report, null, ['status' => ActivityReportStatus::Submitted->value], $request);

        return $report->fresh();
    }

    public function review(MinistryActivityReport $report, User $reviewer, string $notes, ?Request $request = null): MinistryActivityReport
    {
        if ($report->status === ActivityReportStatus::Draft) {
            throw new \DomainException('A draft report must be submitted before review.');
        }

        $report->update([
            'status' => ActivityReportStatus::Reviewed,
            'reviewed_by' => $reviewer->getKey(),
            'reviewed_at' => now(),
            'review_notes' => $notes,
        ]);

        $this->notifications->sendToUser(
            recipient: $report->submitter,
            sender: $reviewer,
            title: 'Activity report reviewed: '.$report->title,
            message: $notes,
            priority: Priority::Normal,
            related: $report,
            actionUrl: route('leader.activity-reports.show', $report),
        );

        $this->audit->log(AuditAction::Approved, $report, null, ['review_notes' => $notes], $request);

        return $report->fresh();
    }
}
