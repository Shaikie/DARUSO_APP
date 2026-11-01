<?php

namespace App\Enums;

/**
 * Standing of a student's academic record.
 *
 * `pending` is the state produced by self-registration until a leader with
 * `student.verify` approves the record. Only verified, active students may use
 * participation features such as complaints, polls and feedback.
 */
enum StudentStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Suspended = 'suspended';
    case Graduated = 'graduated';
    case Expelled = 'expelled';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending verification',
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
            self::Pending => 'text-bg-warning',
            self::Active => 'text-bg-success',
            self::Suspended => 'text-bg-danger',
            self::Graduated => 'text-bg-info',
            self::Expelled => 'text-bg-danger',
            self::Rejected => 'text-bg-dark',
        };
    }

    /**
     * Whether the student may participate in student-only activities.
     */
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
