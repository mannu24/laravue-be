<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Follower extends Model
{
    protected $table = 'followers';

    protected $fillable = [
        'follower_id',
        'following_id'
    ];

    /**
     * Get the user who is following
     */
    public function follower(): BelongsTo
    {
        return $this->belongsTo(User::class, 'follower_id');
    }

    /**
     * Get the user being followed
     */
    public function following(): BelongsTo
    {
        return $this->belongsTo(User::class, 'following_id');
    }

    /**
     * Boot method to track activities
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($follower) {
            $follower->load(['follower', 'following']);
            \App\Models\Activity::createActivity(
                $follower->follower_id,
                \App\Models\Activity::TYPE_FOLLOW_CREATED,
                $follower,
                ($follower->follower->name ?? 'User') . ' started following ' . ($follower->following->name ?? 'someone'),
                [
                    'follower_id' => $follower->follower_id,
                    'following_id' => $follower->following_id,
                ]
            );
        });
    }
}
