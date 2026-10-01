<?php

namespace App\Enums;

/**
 * Finite lifecycle states shared by communication objects.
 */
enum AnnouncementStatus: string
{
    case Draft = 'draft';
    case Review = 'review';
    case Published = 'published';
    case Expired = 'expired';
    case Archived = 'archived';

    /**
     * Statuses a reader is allowed to see in listings.
     */
    public function isVisibleToAudience(): bool
    {
        return $this === self::Published;
    }

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Review => 'In review',
            self::Published => 'Published',
            self::Expired => 'Expired',
            self::Archived => 'Archived',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Draft => 'text-bg-secondary',
            self::Review => 'text-bg-warning',
            self::Published => 'text-bg-success',
            self::Expired => 'text-bg-dark',
            self::Archived => 'text-bg-dark',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
