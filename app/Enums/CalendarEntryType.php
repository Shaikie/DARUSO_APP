<?php

namespace App\Enums;

/**
 * Type of a custom calendar entry.
 *
 * Meetings, events, task deadlines and poll closing dates are aggregated from
 * their own tables; this enum only describes entries created directly on the
 * shared calendar.
 */
enum CalendarEntryType: string
{
    case General = 'general';
    case Academic = 'academic';
    case Deadline = 'deadline';
    case Holiday = 'holiday';

    public function label(): string
    {
        return match ($this) {
            self::General => 'General',
            self::Academic => 'Academic',
            self::Deadline => 'Deadline',
            self::Holiday => 'Holiday',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::General => 'text-bg-secondary',
            self::Academic => 'text-bg-primary',
            self::Deadline => 'text-bg-danger',
            self::Holiday => 'text-bg-success',
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
