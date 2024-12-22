<?php

namespace App\Enum;

class ProjectTypes
{
    const OPEN = 'open';
    const CLOSED = 'closed';

    public static function toArray(): array
    {
        return [
            self::OPEN,
            self::CLOSED,
        ];
    }
}
