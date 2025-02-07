<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Question extends Model
{
    use HasSlug;

    protected $hidden = ['created_at', 'updated_at'];
    protected $appends = ['posted_at', 'owner', 'liked'];
    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'content',
        'content_html',
        'is_solved',
        'score',
        'view_count',
        'last_activity_date',
        'source',
        'source_url',
        'source_question_id',
        'is_closed',
        'closed_reason'
    ];

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
    
    public function getOwnerAttribute() {
        return $this->user_id === auth()->guard('api')->id();
    }
    
    public function getLikedAttribute() {
        return !auth()->guard('api')->check() ? false : $this->likes()->where('user_id', auth()->guard('api')->id())->exists();
    }
    
    public function getPostedAtAttribute() {
        return Carbon::parse($this->created_at)->diffForHumans() ;
    }

    public function likes() {
        return $this->hasMany(Like::class, 'record_id', 'id')->where('likes.record_type', 'question');
    }

    public function toggleLike() {
        if($this->likes()->where('user_id', auth()->guard('api')->id())->exists()) {
            Like::where('user_id', auth()->guard('api')->id())->where('record_id', $this->id)->where('record_type', 'question')->delete();
        } else {
            Like::create([
                'user_id' => auth()->guard('api')->id(),
                'record_id' => $this->id,
                'record_type' => 'question'
            ]);
        }
    }
}
