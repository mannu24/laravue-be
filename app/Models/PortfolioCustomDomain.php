<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PortfolioDomainStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortfolioCustomDomain extends Model
{
    protected $fillable = [
        'portfolio_id', 'domain', 'type', 'status',
        'verified_at', 'last_checked_at', 'dns_error',
    ];

    protected function casts(): array
    {
        return [
            'status' => PortfolioDomainStatus::class,
            'verified_at' => 'datetime',
            'last_checked_at' => 'datetime',
        ];
    }

    public function portfolio(): BelongsTo
    {
        return $this->belongsTo(Portfolio::class);
    }
}
