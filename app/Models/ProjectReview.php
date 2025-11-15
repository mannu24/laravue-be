<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectReview extends Model
{
    protected $fillable = [
        'project_id',
        'user_id',
        'rating',
        'comment',
        'is_verified_purchase',
        'helpful_count',
        'is_featured'
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_verified_purchase' => 'boolean',
        'helpful_count' => 'integer',
        'is_featured' => 'boolean',
    ];

    // Relationships
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified_purchase', true);
    }

    public function scopeByRating($query, int $rating)
    {
        return $query->where('rating', $rating);
    }

    // Methods
    public function markAsHelpful()
    {
        $this->increment('helpful_count');
    }

    public function markAsFeatured()
    {
        $this->update(['is_featured' => true]);
    }
}
