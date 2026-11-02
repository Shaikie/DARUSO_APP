<?php

namespace App\Enums;

/**
 * Standing of a student's academic record.
 *
 * Email verification and academic verification are separate checks. A student
 * remains in a pending state until an authorised leader approves the record.
 */
enum StudentStatus: string
{
    case Pending = 'pending';
    case PendingVerification = 'pending_verification';
    case Active = 'active';
    case Suspended = 'suspended';
    case Graduated = 'graduated';
    case Expelled = 'expelled';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending, self::PendingVerification => 'Pending verification',
            self::Active => 'Active',
            self::Suspended => 'Suspended',
            self::Graduated => 'Graduated',
            self::Expelled => 'Expelled',
            self::Rejected => 'Rejected',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending, self::PendingVerification => 'text-bg-warning',
            self::Active => 'text-bg-success',
            self::Suspended, self::Expelled => 'text-bg-danger',
            self::Graduated => 'text-bg-info',
            self::Rejected => 'text-bg-dark',
        };
    }

    public function canParticipate(): bool
    {
        return $this === self::Active;
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
