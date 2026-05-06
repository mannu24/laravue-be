<?php

declare(strict_types=1);

namespace App\Enums;

enum PortfolioSubscriptionStatus: string
{
    case ACTIVE = 'active';
    case EXPIRED = 'expired';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';
}
