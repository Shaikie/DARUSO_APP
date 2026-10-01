<?php

namespace App\Enums;

/**
 * Who may read a stored document.
 *
 * `Ministry` and `Committee` visibilities are additionally constrained by the
 * viewer's own assignments, so possession of a link is never sufficient.
 */
enum DocumentVisibility: string
{
    case Public = 'public';
    case Students = 'students';
    case Leaders = 'leaders';
    case Ministry = 'ministry';
    case Committee = 'committee';
    case Group = 'group';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Public => 'text-bg-secondary',
            self::Students => 'text-bg-primary',
            self::Leaders => 'text-bg-dark',
            self::Ministry => 'text-bg-info',
            self::Committee => 'text-bg-warning',
            self::Group => 'text-bg-success',
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
