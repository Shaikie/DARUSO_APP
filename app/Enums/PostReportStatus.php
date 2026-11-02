<?php

namespace App\Enums;

/**
 * Moderation state for a reported community post or comment.
 */
enum PostReportStatus: string
{
    case Open = 'open';
    case Reviewing = 'reviewing';
    case Resolved = 'resolved';
    case Dismissed = 'dismissed';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Open',
            self::Reviewing => 'Reviewing',
            self::Resolved => 'Resolved',
            self::Dismissed => 'Dismissed',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Open => 'text-bg-warning',
            self::Reviewing => 'text-bg-info',
            self::Resolved => 'text-bg-success',
            self::Dismissed => 'text-bg-secondary',
        };
    }

    public function isOpen(): bool
    {
        return $this === self::Open || $this === self::Reviewing;
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
