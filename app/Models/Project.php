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
        'is_active'
    ];

    protected $casts = [
        'is_sellable' => 'boolean',
        'is_active' => 'boolean',
        'original_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'views' => 'integer',
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

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function technologies()
    {
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

    // Accessors
    public function getFeaturedImageAttribute()
    {
        return count($this->getMedia('featured_image')) ? $this->getMedia('featured_image')[0]->getFullUrl() : null;
    }

    public function getUpvotesCountAttribute()
    {
        return $this->upvotes()->count();
    }

    public function getIsUpvotedByUserAttribute()
    {
        if (!Auth::check()) return false;
        return $this->upvotes()->where('user_id', Auth::id())->exists();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
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

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
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
}
