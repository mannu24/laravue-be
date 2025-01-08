<?php

namespace App\Enum;

class StatusTypes
{
    const ACTIVE = 'active';
    const INACTIVE = 'inactive';

    public static function toArray(): array
    {
        return [
            self::ACTIVE,
            self::INACTIVE
        ];
    }
}
