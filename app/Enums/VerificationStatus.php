<?php

namespace App\Enums;

/**
 * Administrative verification state of a student academic record.
 *
 * Email confirmation proves control of an address; it does not prove that the
 * person is a registered student. A leader with `student.verify` reviews the
 * submitted record and marks it verified or rejected.
 */
enum VerificationStatus: string
{
    case Pending = 'pending';
    case Verified = 'verified';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Awaiting verification',
            self::Verified => 'Verified',
            self::Rejected => 'Rejected',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending => 'text-bg-warning',
            self::Verified => 'text-bg-success',
            self::Rejected => 'text-bg-danger',
        };
    }

    public function isVerified(): bool
    {
        return $this === self::Verified;
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
