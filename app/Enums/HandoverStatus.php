<?php

namespace App\Enums;

/**
 * Lifecycle of a leadership handover record.
 *
 * A handover is drafted by the outgoing officer, submitted, then acknowledged by
 * the incoming officer. Records are never deleted so institutional knowledge is
 * preserved across terms.
 */
enum HandoverStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case Acknowledged = 'acknowledged';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Submitted => 'Submitted',
            self::Acknowledged => 'Acknowledged',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Draft => 'text-bg-secondary',
            self::Submitted => 'text-bg-warning',
            self::Acknowledged => 'text-bg-success',
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
