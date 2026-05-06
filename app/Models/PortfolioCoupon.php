<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PortfolioCoupon extends Model
{
    protected $fillable = [
        'code',
        'discount_type',
        'discount_value',
        'max_uses',
        'max_uses_per_user',
        'used_count',
        'min_order_amount',
        'applicable_plans',
        'starts_at',
        'expires_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2',
            'min_order_amount' => 'decimal:2',
            'applicable_plans' => 'json',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Check if the coupon is currently valid (ignoring per-user limits).
     */
    public function isValid(): bool
    {
        if (!$this->is_active) return false;
        if ($this->starts_at && now()->lt($this->starts_at)) return false;
        if ($this->expires_at && now()->gt($this->expires_at)) return false;
        if ($this->max_uses !== null && $this->used_count >= $this->max_uses) return false;
        return true;
    }

    /**
     * Check if a specific user can use this coupon.
     */
    public function isValidForUser(int $userId): bool
    {
        if (!$this->isValid()) return false;

        $userUses = PortfolioCouponUse::where('coupon_id', $this->id)
            ->where('user_id', $userId)
            ->count();

        return $userUses < $this->max_uses_per_user;
    }

    /**
     * Check if coupon applies to a specific plan.
     */
    public function appliesToPlan(string $planSlug): bool
    {
        if (empty($this->applicable_plans)) return true;
        return in_array($planSlug, $this->applicable_plans);
    }

    /**
     * Calculate discount for a given amount.
     */
    public function calculateDiscount(float $amount): float
    {
        if ($this->min_order_amount && $amount < $this->min_order_amount) {
            return 0;
        }

        if ($this->discount_type === 'percentage') {
            return round($amount * ($this->discount_value / 100), 2);
        }

        // Fixed discount — cannot exceed order amount
        return min((float) $this->discount_value, $amount);
    }
}
