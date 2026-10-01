<?php

namespace App\Enums;

/**
 * Lifecycle of the student complaint workflow.
 */
enum ComplaintStatus: string
{
    case Submitted = 'submitted';
    case UnderReview = 'under_review';
    case Forwarded = 'forwarded';
    case InProgress = 'in_progress';
    case AwaitingStudent = 'awaiting_student';
    case Resolved = 'resolved';
    case Closed = 'closed';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Submitted => 'Submitted',
            self::UnderReview => 'Under review',
            self::Forwarded => 'Forwarded',
            self::InProgress => 'In progress',
            self::AwaitingStudent => 'Awaiting student',
            self::Resolved => 'Resolved',
            self::Closed => 'Closed',
            self::Rejected => 'Rejected',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Submitted => 'text-bg-secondary',
            self::UnderReview, self::Forwarded => 'text-bg-info',
            self::InProgress => 'text-bg-primary',
            self::AwaitingStudent => 'text-bg-warning',
            self::Resolved => 'text-bg-success',
            self::Closed, self::Rejected => 'text-bg-dark',
        };
    }

    /**
     * Statuses that close a complaint; no further transition is expected.
     */
    public function isTerminal(): bool
    {
        return in_array($this, [self::Resolved, self::Closed, self::Rejected], true);
    }

    /**
     * Allowed forward transitions for the complaint workflow.
     *
     * @return array<int, self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Submitted => [self::UnderReview, self::Forwarded, self::Rejected],
            self::UnderReview => [self::Forwarded, self::InProgress, self::AwaitingStudent, self::Rejected],
            self::Forwarded => [self::UnderReview, self::InProgress, self::Rejected],
            self::InProgress => [self::AwaitingStudent, self::Resolved],
            self::AwaitingStudent => [self::InProgress, self::Resolved, self::Rejected],
            self::Resolved => [self::Closed, self::InProgress],
            self::Closed => [],
            self::Rejected => [self::UnderReview],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
