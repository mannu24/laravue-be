<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Portfolio extends Model
{
    protected $fillable = [
        'user_id',
        'subdomain',
        'template_slug',
        'is_published',
        'title',
        'tagline',
        'bio',
        'location_city',
        'location_country',
        'available_for_hire',
        'resume_path',
        'photo_path',
        'meta_title',
        'meta_description',
        'og_image_path',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'available_for_hire' => 'boolean',
            'settings' => 'json',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function socialLinks(): HasMany
    {
        return $this->hasMany(PortfolioSocialLink::class)->orderBy('sort_order');
    }

    public function skills(): HasMany
    {
        return $this->hasMany(PortfolioSkill::class)->orderBy('sort_order');
    }

    public function experiences(): HasMany
    {
        return $this->hasMany(PortfolioExperience::class)->orderBy('sort_order');
    }

    public function educations(): HasMany
    {
        return $this->hasMany(PortfolioEducation::class)->orderBy('sort_order');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(PortfolioProject::class)->orderBy('sort_order');
    }

    public function testimonials(): HasMany
    {
        return $this->hasMany(PortfolioTestimonial::class)->orderBy('sort_order');
    }

    public function customSections(): HasMany
    {
        return $this->hasMany(PortfolioCustomSection::class)->orderBy('sort_order');
    }

    public function customDomain(): HasOne
    {
        return $this->hasOne(PortfolioCustomDomain::class);
    }

    /**
     * Get the active subscription for this portfolio's user.
     */
    public function activeSubscription(): ?PortfolioSubscription
    {
        return PortfolioSubscription::where('user_id', $this->user_id)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->first();
    }

    /**
     * Check if the portfolio has an active (or grace period) subscription.
     */
    public function hasActiveAccess(): bool
    {
        return PortfolioSubscription::where('user_id', $this->user_id)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->where('expires_at', '>', now())
                  ->orWhere('grace_ends_at', '>', now());
            })
            ->exists();
    }

    /**
     * Check if the portfolio is in grace period.
     */
    public function isInGracePeriod(): bool
    {
        return PortfolioSubscription::where('user_id', $this->user_id)
            ->where('status', 'active')
            ->where('expires_at', '<=', now())
            ->where('grace_ends_at', '>', now())
            ->exists();
    }

    /**
     * Get the full subdomain URL.
     */
    public function getSubdomainUrlAttribute(): string
    {
        $domain = config('portfolio.domain', 'laravue.in');
        $scheme = app()->environment('production') ? 'https' : 'http';
        return "{$scheme}://{$this->subdomain}.{$domain}";
    }
}
