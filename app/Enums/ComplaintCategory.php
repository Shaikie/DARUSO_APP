<?php

namespace App\Enums;

/**
 * Complaint categories double as the routing hint used when a leader assigns a
 * complaint to a ministry. They are enumerated so that reporting can group on
 * a fixed vocabulary.
 */
enum ComplaintCategory: string
{
    case Academic = 'academic';
    case Hostel = 'hostel';
    case Welfare = 'welfare';
    case Discipline = 'discipline';
    case Finance = 'finance';
    case Infrastructure = 'infrastructure';
    case Health = 'health';
    case Other = 'other';

    public function label(): string
    {
        return ucwords(str_replace('_', ' ', $this->value));
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
