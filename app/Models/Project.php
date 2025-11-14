<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
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
    }
}
