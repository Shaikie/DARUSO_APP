<?php

namespace App\Enums;

/**
 * Progress state for a leadership task.
 */
enum TaskStatus: string
{
    case Todo = 'todo';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Todo => 'To do',
            self::InProgress => 'In progress',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Todo => 'text-bg-warning',
            self::InProgress => 'text-bg-info',
            self::Completed => 'text-bg-success',
            self::Cancelled => 'text-bg-secondary',
        };
    }

    public function isClosed(): bool
    {
        return $this === self::Completed || $this === self::Cancelled;
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
