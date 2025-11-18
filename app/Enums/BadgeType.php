<?php

declare(strict_types=1);

namespace App\Enums;

enum BadgeType: string
{
    case PARTICIPATION = 'participation';
    case CONSISTENCY = 'consistency';
    case QUALITY = 'quality';
    case CONTRIBUTION = 'contribution';
    case RARE = 'rare';
    case EVENT = 'event';

    /**
     * Get all enum values as array.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get all enum cases as array.
     */
    public static function toArray(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}

