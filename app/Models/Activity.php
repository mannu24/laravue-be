<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Activity extends Model
{
    protected $table = 'user_activities';

    protected $fillable = [
        'user_id',
        'activity_type',
        'subject_type',
        'subject_id',
        'description',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who performed the activity
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the subject of the activity (polymorphic)
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Activity types constants
     */
    const TYPE_POST_CREATED = 'post_created';
    const TYPE_QUESTION_CREATED = 'question_created';
    const TYPE_ANSWER_CREATED = 'answer_created';
    const TYPE_COMMENT_CREATED = 'comment_created';
    const TYPE_LIKE_CREATED = 'like_created';
    const TYPE_FOLLOW_CREATED = 'follow_created';
    const TYPE_UPVOTE_CREATED = 'upvote_created';

    /**
     * Create an activity record
     */
    public static function createActivity(
        int $userId,
        string $activityType,
        Model $subject,
        ?string $description = null,
        ?array $metadata = null
    ): self {
        return self::create([
            'user_id' => $userId,
            'activity_type' => $activityType,
            'subject_type' => get_class($subject),
            'subject_id' => $subject->id,
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }
}
