<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Project extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $table = 'projects';

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'description',
        'project_type',
        'github_url',
        'demo_url',
        'is_sellable',
        'original_price',
        'selling_price',
        'views',
        'is_active',
        // Status & Workflow
        'status',
        'published_at',
        'rejected_at',
        'rejection_reason',
        // SEO
        'meta_title',
        'meta_description',
        'meta_keywords',
        'excerpt',
        // Content
        'short_description',
        'long_description',
        'features',
        'requirements',
        'installation_guide',
        'changelog',
        'version',
        'license_type',
        'license_url',
        'documentation_url',
        'support_url',
        // Media
        'screenshot_count',
        'video_count',
        'gallery_count',
        // Analytics
        'unique_views',
        'downloads_count',
        'purchases_count',
        'conversion_rate',
        'avg_rating',
        'ratings_count',
        'comments_count',
        'shares_count',
        'bookmarks_count',
        'last_viewed_at',
        'trending_score',
        // Commerce
        'currency',
        'discount_percentage',
        'discount_start_date',
        'discount_end_date',
        'is_featured',
        'featured_until',
        'sales_count',
        'revenue',
        'commission_rate',
        'affiliate_enabled',
        'affiliate_commission',
        'stock_quantity',
        'is_digital',
        'delivery_method',
        // Quality
        'is_verified',
        'quality_score',
        'moderation_status',
        'moderated_at',
        'moderated_by',
        'moderation_notes',
        'spam_score',
        'is_premium',
        // Categorization
        'category_id',
        'difficulty_level',
        'estimated_build_time',
        'industry',
        'tags',
        // UX
        'language',
        'read_time',
        'last_updated_at',
        'update_frequency',
        'is_maintained',
        'maintenance_status',
        'deprecation_notice',
        'migration_guide_url',
        // Social
        'reviews_count',
        'discussions_count',
        'contributors_count',
        'stars_count',
        'forks_count',
        // Performance
        'cache_key',
        'indexed_at',
        'popularity_score',
        'trending_rank',
        'featured_rank',
        'last_calculated_at',
        // Version
        'current_version',
        'latest_version_released_at',
        // Legal
        'terms_accepted_at',
        'privacy_policy_url',
        'terms_of_service_url',
        'gdpr_compliant',
        'data_retention_days',
        'age_restriction',
        'geographic_restrictions',
        // Notifications
        'notification_preferences',
        'last_notification_sent_at',
        'subscribers_count',
    ];

    protected $casts = [
        'is_sellable' => 'boolean',
        'is_active' => 'boolean',
        'original_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'views' => 'integer',
        'published_at' => 'datetime',
        'rejected_at' => 'datetime',
        'features' => 'array',
        'tags' => 'array',
        'geographic_restrictions' => 'array',
        'notification_preferences' => 'array',
        'screenshot_count' => 'integer',
        'video_count' => 'integer',
        'gallery_count' => 'integer',
        'unique_views' => 'integer',
        'downloads_count' => 'integer',
        'purchases_count' => 'integer',
        'conversion_rate' => 'decimal:2',
        'avg_rating' => 'decimal:2',
        'ratings_count' => 'integer',
        'comments_count' => 'integer',
        'shares_count' => 'integer',
        'bookmarks_count' => 'integer',
        'last_viewed_at' => 'datetime',
        'trending_score' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'discount_start_date' => 'date',
        'discount_end_date' => 'date',
        'is_featured' => 'boolean',
        'featured_until' => 'datetime',
        'sales_count' => 'integer',
        'revenue' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'affiliate_enabled' => 'boolean',
        'affiliate_commission' => 'decimal:2',
        'stock_quantity' => 'integer',
        'is_digital' => 'boolean',
        'is_verified' => 'boolean',
        'quality_score' => 'integer',
        'moderated_at' => 'datetime',
        'spam_score' => 'integer',
        'is_premium' => 'boolean',
        'read_time' => 'integer',
        'last_updated_at' => 'datetime',
        'is_maintained' => 'boolean',
        'reviews_count' => 'integer',
        'discussions_count' => 'integer',
        'contributors_count' => 'integer',
        'stars_count' => 'integer',
        'forks_count' => 'integer',
        'indexed_at' => 'datetime',
        'popularity_score' => 'decimal:2',
        'trending_rank' => 'integer',
        'featured_rank' => 'integer',
        'last_calculated_at' => 'datetime',
        'latest_version_released_at' => 'datetime',
        'terms_accepted_at' => 'datetime',
        'gdpr_compliant' => 'boolean',
        'data_retention_days' => 'integer',
        'age_restriction' => 'integer',
        'last_notification_sent_at' => 'datetime',
        'subscribers_count' => 'integer',
    ];

    protected $appends = [
        'featured_image',
        'upvotes_count',
        'is_upvoted_by_user'
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($project) {
            $project->slug = self::generateUniqueSlug($project->title);
            if (!$project->user_id && Auth::check()) {
                $project->user_id = Auth::id();
            }
        });

        static::updating(function ($project) {
            if ($project->isDirty('title')) {
                $project->slug = self::generateUniqueSlug($project->title);
            }
        });
    }

    public static function generateUniqueSlug($title)
    {
        $slug = Str::slug($title);
        $randomStr = Str::random(10);
        return "{$slug}-{$randomStr}";
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function technologies()
    {
        return $this->belongsToMany(Technology::class, 'project_technologies', 'project_id', 'technology_id');
    }

    /**
     * Get bookmarks for this project
     */
    public function bookmarks()
    {
        return $this->morphMany(Bookmark::class, 'record');
    }

    /**
     * Check if the authenticated user has bookmarked this project
     */
    public function getBookmarkedAttribute()
    {
        if (!auth()->guard('api')->check()) {
            return false;
        }
        return $this->bookmarks()->where('user_id', auth()->guard('api')->id())->exists();
    }

    /**
     * Get bookmark count for this project
     */
    public function getBookmarkCountAttribute()
    {
        return $this->bookmarks()->count();
        return $this->belongsToMany(Technology::class, 'project_technologies');
    }

    public function upvotes()
    {
        return $this->morphMany(Upvote::class, 'record');
    }

    public function funds()
    {
        return $this->hasMany(ProjectFund::class);
    }

    public function category()
    {
        return $this->belongsTo(ProjectCategory::class);
    }

    public function reviews()
    {
        return $this->hasMany(ProjectReview::class);
    }

    public function versions()
    {
        return $this->hasMany(ProjectVersion::class);
    }

    public function moderator()
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    // Accessors
    public function getFeaturedImageAttribute()
    {
        return count($this->getMedia('featured_image')->toArray()) ? $this->getMedia('featured_image')[0]->getFullUrl() : null;
    }

    public function getUpvotesCountAttribute()
    {
        return $this->upvotes()->count();
    }

    public function getIsUpvotedByUserAttribute()
    {
        if (!auth()->guard('api')->check()) {
            return false;
        }
        return $this->upvotes()->where('user_id', auth()->guard('api')->id())->exists();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true)
            ->where(function ($q) {
                $q->whereNull('featured_until')
                  ->orWhere('featured_until', '>', now());
            });
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopePremium($query)
    {
        return $query->where('is_premium', true);
    }

    public function scopeOpenSource($query)
    {
        return $query->where('project_type', 'open');
    }

    public function scopeSellable($query)
    {
        return $query->where('is_sellable', true);
    }

    public function scopePopular($query)
    {
        return $query->withCount('upvotes')->orderBy('upvotes_count', 'desc');
    }

    public function scopeTrending($query)
    {
        return $query->orderBy('trending_score', 'desc');
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByDifficulty($query, $difficulty)
    {
        return $query->where('difficulty_level', $difficulty);
    }

    public function scopeByIndustry($query, $industry)
    {
        return $query->where('industry', $industry);
    }

    public function scopeOnDiscount($query)
    {
        return $query->whereNotNull('discount_percentage')
            ->where('discount_start_date', '<=', now())
            ->where(function ($q) {
                $q->whereNull('discount_end_date')
                  ->orWhere('discount_end_date', '>=', now());
            });
    }

    // Methods
    public function incrementViews()
    {
        $this->increment('views');
    }

    public function toggleUpvote($userId)
    {
        $existingUpvote = $this->upvotes()->where('user_id', $userId)->first();
        
        if ($existingUpvote) {
            $existingUpvote->delete();
            return false; // removed
        } else {
            $this->upvotes()->create(['user_id' => $userId]);
            return true; // added
        }
    }

    // Status Methods
    public function publish()
    {
        $this->update([
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    public function reject($reason = null)
    {
        $this->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }

    public function archive()
    {
        $this->update(['status' => 'archived']);
    }

    public function submitForReview()
    {
        $this->update(['status' => 'pending']);
    }

    // Media Methods
    public function getScreenshots()
    {
        return $this->getMedia('screenshots');
    }

    public function getVideos()
    {
        return $this->getMedia('videos');
    }

    public function getGallery()
    {
        return $this->getMedia('gallery');
    }

    // Analytics Methods
    public function incrementUniqueViews()
    {
        $this->increment('unique_views');
        $this->update(['last_viewed_at' => now()]);
    }

    public function incrementDownloads()
    {
        $this->increment('downloads_count');
    }

    public function incrementPurchases()
    {
        $this->increment('purchases_count');
        $this->recalculateConversionRate();
    }

    public function recalculateConversionRate()
    {
        if ($this->getAttribute('views') > 0) {
            $this->setAttribute('conversion_rate', ($this->getAttribute('purchases_count') / $this->getAttribute('views')) * 100);
            $this->save();
        }
    }

    public function updateRating()
    {
        $avgRating = $this->reviews()->avg('rating');
        $ratingsCount = $this->reviews()->count();
        
        $this->update([
            'avg_rating' => round($avgRating, 2),
            'ratings_count' => $ratingsCount,
        ]);
    }

    // Commerce Methods
    public function isOnDiscount()
    {
        if (!$this->getAttribute('discount_percentage')) {
            return false;
        }

        $now = now();
        return $this->getAttribute('discount_start_date') <= $now &&
               ($this->getAttribute('discount_end_date') === null || $this->getAttribute('discount_end_date') >= $now);
    }

    public function getDiscountedPrice()
    {
        if (!$this->isOnDiscount() || !$this->getAttribute('selling_price')) {
            return $this->getAttribute('selling_price');
        }

        $discount = ($this->getAttribute('selling_price') * $this->getAttribute('discount_percentage')) / 100;
        return $this->getAttribute('selling_price') - $discount;
    }

    public function isInStock()
    {
        if ($this->getAttribute('stock_quantity') === null) {
            return true; // Unlimited stock
        }
        return $this->getAttribute('stock_quantity') > 0;
    }

    // Quality Methods
    public function verify()
    {
        $this->update(['is_verified' => true]);
    }

    public function unverify()
    {
        $this->update(['is_verified' => false]);
    }

    public function feature($until = null)
    {
        $this->update([
            'is_featured' => true,
            'featured_until' => $until,
        ]);
    }

    public function unfeature()
    {
        $this->update([
            'is_featured' => false,
            'featured_until' => null,
        ]);
    }

    // Version Methods
    public function getLatestVersion()
    {
        return $this->versions()->latest('release_date')->first();
    }

    public function getStableVersion()
    {
        return $this->versions()->stable()->latest('release_date')->first();
    }
}
