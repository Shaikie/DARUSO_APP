<?php

namespace App\Enums;

/**
 * Triage state for a student suggestion or feedback item.
 */
enum FeedbackStatus: string
{
    case Open = 'open';
    case InReview = 'in_review';
    case Resolved = 'resolved';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Open',
            self::InReview => 'In review',
            self::Resolved => 'Resolved',
            self::Closed => 'Closed',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Open => 'text-bg-warning',
            self::InReview => 'text-bg-info',
            self::Resolved => 'text-bg-success',
            self::Closed => 'text-bg-secondary',
        };
    }

    public function isOpen(): bool
    {
        return $this === self::Open || $this === self::InReview;
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
