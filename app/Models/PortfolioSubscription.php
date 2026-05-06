<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PortfolioSubscriptionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortfolioSubscription extends Model
{
    protected $fillable = [
        'user_id',
        'plan_id',
        'order_id',
        'starts_at',
        'expires_at',
        'grace_ends_at',
        'status',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'grace_ends_at' => 'datetime',
            'status' => PortfolioSubscriptionStatus::class,
            'cancelled_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(PortfolioPlan::class, 'plan_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(PortfolioOrder::class, 'order_id');
    }

    public function isActive(): bool
    {
        return $this->status === PortfolioSubscriptionStatus::ACTIVE
            && ($this->expires_at->isFuture() || ($this->grace_ends_at && $this->grace_ends_at->isFuture()));
    }

    public function isInGracePeriod(): bool
    {
        return $this->status === PortfolioSubscriptionStatus::ACTIVE
            && $this->expires_at->isPast()
            && $this->grace_ends_at
            && $this->grace_ends_at->isFuture();
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->where('expires_at', '>', now())
                  ->orWhere('grace_ends_at', '>', now());
            });
    }
}
