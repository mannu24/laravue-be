<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'notifiable_id',
        'type',
        'title',
        'message',
        'subject_type',
        'subject_id',
        'data',
        'read',
        'email_sent',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read' => 'boolean',
        'email_sent' => 'boolean',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Notification types constants
     */
    const TYPE_POST_LIKED = 'post_liked';
    const TYPE_POST_COMMENTED = 'post_commented';
    const TYPE_COMMENT_LIKED = 'comment_liked';
    const TYPE_QUESTION_LIKED = 'question_liked';
    const TYPE_QUESTION_UPVOTED = 'question_upvoted';
    const TYPE_QUESTION_ANSWERED = 'question_answered';
    const TYPE_ANSWER_UPVOTED = 'answer_upvoted';
    const TYPE_ANSWER_REPLIED = 'answer_replied';
    const TYPE_ANSWER_ACCEPTED = 'answer_accepted';
    const TYPE_MENTIONED = 'mentioned';
    const TYPE_FOLLOWED = 'followed';

    /**
     * Get the user who receives the notification
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who triggered the notification
     */
    public function notifiable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'notifiable_id');
    }

    /**
     * Get the subject of the notification (polymorphic)
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(): void
    {
        if (!$this->read) {
            $this->update([
                'read' => true,
                'read_at' => now(),
            ]);
        }
    }

    /**
     * Mark notification as unread
     */
    public function markAsUnread(): void
    {
        $this->update([
            'read' => false,
            'read_at' => null,
        ]);
    }
}
