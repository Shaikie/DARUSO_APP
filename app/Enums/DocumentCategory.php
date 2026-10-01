<?php

namespace App\Enums;

enum DocumentCategory: string
{
    case Constitution = 'constitution';
    case Policy = 'policy';
    case Minutes = 'minutes';
    case Financial = 'financial';
    case Academic = 'academic';
    case Welfare = 'welfare';
    case Form = 'form';
    case Report = 'report';
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
