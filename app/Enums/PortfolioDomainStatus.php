<?php

declare(strict_types=1);

namespace App\Enums;

enum PortfolioDomainStatus: string
{
    case PENDING = 'pending';
    case VERIFIED = 'verified';
    case FAILED = 'failed';
}
