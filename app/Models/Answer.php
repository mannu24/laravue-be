<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{

    protected $fillable = [
        'question_id',
        'parent_id',
        'user_id',
        'content',
        'content_html',
        'is_accepted',
        'score',
        'comment_count',
        'last_activity_date',
        'source',
        'source_url',
        'source_answer_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function upvotes()
    {
        return $this->morphMany(Upvote::class, 'record');
    }

    // Relationship for parent answer
    public function parent()
    {
        return $this->belongsTo(Answer::class, 'parent_id');
    }

    // Relationship for child answers (replies)
    public function replies()
    {
        return $this->hasMany(Answer::class, 'parent_id')->latest();
    }

    /**
     * Boot method to track activities
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($answer) {
            $answer->load(['user', 'question']);
            \App\Models\Activity::createActivity(
                $answer->user_id,
                \App\Models\Activity::TYPE_ANSWER_CREATED,
                $answer,
                ($answer->user->name ?? 'User') . ' answered a question',
                [
                    'question_id' => $answer->question_id,
                    'question_title' => $answer->question->title ?? null,
                ]
            );
        });
    }
}
