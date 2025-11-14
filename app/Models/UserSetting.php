<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSetting extends Model
{
    protected $fillable = [
        'user_id',
        'email_notifications',
        'push_notifications',
        'notification_preferences',
    ];

    protected $casts = [
        'email_notifications' => 'boolean',
        'push_notifications' => 'boolean',
        'notification_preferences' => 'array',
    ];

    /**
     * Get the user that owns the settings
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get or create settings for a user
     */
    public static function getOrCreateForUser(int $userId): self
    {
        return self::firstOrCreate(
            ['user_id' => $userId],
            [
                'email_notifications' => true,
                'push_notifications' => true,
                'notification_preferences' => self::getDefaultPreferences(),
            ]
        );
    }

    /**
     * Get default notification preferences
     */
    public static function getDefaultPreferences(): array
    {
        return [
            'post_liked' => true,
            'post_commented' => true,
            'comment_liked' => true,
            'question_liked' => true,
            'question_upvoted' => true,
            'question_answered' => true,
            'answer_upvoted' => true,
            'answer_replied' => true,
            'mentioned' => true,
            'followed' => true,
        ];
    }
}
