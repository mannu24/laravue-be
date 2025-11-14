<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Bookmark extends Model
{
    protected $table = 'bookmarks';

    protected $fillable = [
        'user_id',
        'record_id',
        'record_type',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who bookmarked the record
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the bookmarked record (polymorphic)
     */
    public function record(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Check if a user has bookmarked a specific record
     */
    public static function isBookmarked(int $userId, string $recordType, int $recordId): bool
    {
        return self::where('user_id', $userId)
            ->where('record_type', $recordType)
            ->where('record_id', $recordId)
            ->exists();
    }

    /**
     * Get bookmark count for a specific record
     */
    public static function getBookmarkCount(string $recordType, int $recordId): int
    {
        return self::where('record_type', $recordType)
            ->where('record_id', $recordId)
            ->count();
    }
}
