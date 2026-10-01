<?php

namespace App\Enums;

/**
 * Seeded role identifiers. These are database-driven, but the values are fixed
 * because application code (navigation, routing, policies) branches on them.
 * Nothing here bypasses authorization: each role is only a bundle of
 * permissions, all of which are enforced individually.
 */
enum RoleName: string
{
    case Administrator = 'admin';
    case SecretaryGeneral = 'secretary_general';
    case MinistryLeader = 'ministry_leader';
    case CommitteeLeader = 'committee_leader';
    case Student = 'student';

    public function label(): string
    {
        return match ($this) {
            self::Administrator => 'System Administrator',
            self::SecretaryGeneral => 'Secretary General',
            self::MinistryLeader => 'Ministry Leader',
            self::CommitteeLeader => 'Committee Leader',
            self::Student => 'Student',
        };
    }

    /**
     * Roles considered "leadership" for navigation and dashboard routing.
     */
    public function isLeadership(): bool
    {
        return $this !== self::Student;
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * @return array<int, string>
     */
    public static function leadershipValues(): array
    {
        return array_values(array_filter(
            self::values(),
            fn (string $value): bool => $value !== self::Student->value,
        ));
    }
}
