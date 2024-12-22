<?php

namespace App\Enum;

class PaymentStatusTyeps
{
    const PAID = 'paid';
    const FAILED = 'failed';
    const REFUNDED = 'refunded';
    const PENDING = 'pending';
    const DECLINED = 'declined';
    const CANCELLED = 'cancelled';

    public static function toArray(): array
    {
        return [
            self::PAID,
            self::FAILED,
            self::REFUNDED,
            self::PENDING,
            self::DECLINED,
            self::CANCELLED,
        ];
    }
}
