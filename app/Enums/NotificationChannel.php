<?php

namespace App\Enums;

/**
 * Delivery channels a user can opt in or out of per category.
 *
 * In-app delivery is always recorded in the database; the preference controls
 * whether the dispatcher additionally attempts email delivery.
 */
enum NotificationChannel: string
{
    case InApp = 'in_app';
    case Email = 'email';

    public function label(): string
    {
        return match ($this) {
            self::InApp => 'In-app',
            self::Email => 'Email',
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
