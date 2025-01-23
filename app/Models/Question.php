<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Question extends Model
{
    use HasSlug;

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
}
