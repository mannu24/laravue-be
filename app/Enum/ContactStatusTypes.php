<?php

namespace App\Enum;

class ContactStatusTypes
{
    const PENDING = 'pending';
    const RESPONDED = 'responded';
    const REVIEWED = 'reviewed';
    const RESOLVED = 'resolved';

    public static function toArray(): array
    {
        return [
            self::PENDING,
            self::RESPONDED,
            self::REVIEWED,
            self::RESOLVED,
        ];
    }
}
