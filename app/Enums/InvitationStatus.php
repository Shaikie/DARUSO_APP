<?php

namespace App\Enums;

/**
 * Lifecycle of a leadership invitation.
 *
 * Tokens are single-use. Accepted, declined, revoked and expired invitations are
 * terminal and can never be re-activated.
 */
enum InvitationStatus: string
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Declined = 'declined';
    case Revoked = 'revoked';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Accepted => 'Accepted',
            self::Declined => 'Declined',
            self::Revoked => 'Revoked',
            self::Expired => 'Expired',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending => 'text-bg-warning',
            self::Accepted => 'text-bg-success',
            self::Declined => 'text-bg-secondary',
            self::Revoked => 'text-bg-danger',
            self::Expired => 'text-bg-dark',
        };
    }

    public function isTerminal(): bool
    {
        return $this !== self::Pending;
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
