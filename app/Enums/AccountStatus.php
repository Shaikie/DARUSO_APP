<?php

namespace App\Enums;

/**
 * Lifecycle of an authentication account.
 *
 * Registration starts `pending` until the email address is confirmed. An
 * administrator may later `suspend` or `reject` an account; both states block
 * authentication but preserve the record and its history.
 */
enum AccountStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Suspended = 'suspended';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Active => 'Active',
            self::Suspended => 'Suspended',
            self::Rejected => 'Rejected',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending => 'text-bg-warning',
            self::Active => 'text-bg-success',
            self::Suspended => 'text-bg-danger',
            self::Rejected => 'text-bg-dark',
        };
    }

    /**
     * Whether the account may authenticate.
     */
    public function canLogin(): bool
    {
        return $this === self::Pending || $this === self::Active;
    }

    /**
     * Whether the account is fully approved for routine use.
     */
    public function isApproved(): bool
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
