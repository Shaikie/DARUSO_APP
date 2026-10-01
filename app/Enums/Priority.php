<?php

namespace App\Enums;

/**
 * Communication priority, shared by announcements and notifications.
 */
enum Priority: string
{
    case Low = 'low';
    case Normal = 'normal';
    case High = 'high';
    case Urgent = 'urgent';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Low => 'text-bg-secondary',
            self::Normal => 'text-bg-primary',
            self::High => 'text-bg-warning',
            self::Urgent => 'text-bg-danger',
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
