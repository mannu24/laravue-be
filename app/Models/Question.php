<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Question extends Model
{
    use HasFactory, HasSlug;

    protected $hidden = ['created_at', 'updated_at'];
    protected $appends = ['posted_at', 'owner', 'liked', 'bookmarked', 'bookmark_count'];
    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'body',
        'content',
        'content_html',
        'ai_generated_summary',
        'views',
        'view_count',
        'is_solved',
        'score',
        'last_activity_date',
        'source',
        'source_url',
        'source_question_id',
        'is_closed',
        'closed_reason'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'views' => 'integer',
            'view_count' => 'integer',
            'is_solved' => 'boolean',
            'score' => 'integer',
            'is_closed' => 'boolean',
            'last_activity_date' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    // generating slug
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function upvotes()
    {
        return $this->morphMany(Upvote::class, 'record');
    }

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    public function getOwnerAttribute()
    {
        return $this->user_id === auth()->guard('api')->id();
    }

    public function getLikedAttribute()
    {
        return !auth()->guard('api')->check() ? false : $this->likes()->where('user_id', auth()->guard('api')->id())->exists();
    }

    public function getPostedAtAttribute()
    {
        return Carbon::parse($this->created_at)->diffForHumans();
    }

    public function likes()
    {
        return $this->hasMany(Like::class, 'record_id', 'id')->where('likes.record_type', 'question');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'tag_associates', 'record_id', 'tag_id');
    }

    /**
     * Get bookmarks for this question
     */
    public function bookmarks()
    {
        return $this->morphMany(Bookmark::class, 'record');
    }

    /**
     * Check if the authenticated user has bookmarked this question
     */
    public function getBookmarkedAttribute()
    {
        if (!auth()->guard('api')->check()) {
            return false;
        }
        return $this->bookmarks()->where('user_id', auth()->guard('api')->id())->exists();
    }

    /**
     * Get bookmark count for this question
     */
    public function getBookmarkCountAttribute()
    {
        return $this->bookmarks()->count();
    }

    public function toggleLike()
    {
        if ($this->likes()->where('user_id', auth()->guard('api')->id())->exists()) {
            Like::where('user_id', auth()->guard('api')->id())->where('record_id', $this->id)->where('record_type', 'question')->delete();
            return false; // Return false when unliked
        } else {
            Like::create([
                'user_id' => auth()->guard('api')->id(),
                'record_id' => $this->id,
                'record_type' => 'question'
            ]);
            return true; // Return true when liked
        }
    }

    /**
     * Boot method to track activities
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($question) {
            $question->load('user');
            \App\Models\Activity::createActivity(
                $question->user_id,
                \App\Models\Activity::TYPE_QUESTION_CREATED,
                $question,
                ($question->user->name ?? 'User') . ' asked a new question',
                [
                    'title' => $question->title,
                    'slug' => $question->slug,
                ]
            );
        });
    }
}
