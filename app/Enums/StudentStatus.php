<?php

namespace App\Enums;

enum StudentStatus: string
{
    case PendingVerification = 'pending_verification';
    case Active = 'active';
    case Suspended = 'suspended';
    case Graduated = 'graduated';
    case Expelled = 'expelled';

    public function label(): string
    {
        return match ($this) {
            self::PendingVerification => 'Pending verification',
            default => ucfirst($this->value),
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::PendingVerification => 'text-bg-secondary',
            self::Active => 'text-bg-success',
            self::Suspended => 'text-bg-warning',
            self::Graduated => 'text-bg-info',
            self::Expelled => 'text-bg-danger',
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
