<?php

namespace App\Services;

use App\Models\Bookmark;
use App\Models\Post;
use App\Models\Question;
use App\Models\Project;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class BookmarkService
{
    /**
     * Allowed bookmarkable types
     */
    private const ALLOWED_TYPES = [
        'post' => Post::class,
        'question' => Question::class,
        'project' => Project::class,
    ];

    /**
     * Toggle bookmark status for a record
     * 
     * @param int $userId The user ID
     * @param string $type The record type (post, question, project)
     * @param int $recordId The record ID
     * @return array Returns ['bookmarked' => bool, 'bookmark' => Bookmark|null, 'count' => int]
     * @throws InvalidArgumentException
     */
    public static function toggle(int $userId, string $type, string $recordId): array
    {
        $modelClass = self::ALLOWED_TYPES[$type];

        // Verify record exists
        switch ($type) {
            case 'post':
                $record = Post::where('post_code', $recordId)->first();
                break;
            case 'question':
                $record = Question::where('slug', $recordId)->first();
                break;
            case 'project':
                $record = Project::where('project_code', $recordId)->first();
                break;
            default:
                throw new InvalidArgumentException("Invalid bookmark type: {$type}. Allowed types: " . implode(', ', array_keys(self::ALLOWED_TYPES)));
        }

        if (!$record) {
            throw new InvalidArgumentException("{$type} with ID {$recordId} not found.");
        }

        // Check if already bookmarked
        $bookmark = Bookmark::where('user_id', $userId)
            ->where('record_type', $modelClass)
            ->where('record_id', $record->id)
            ->first();

        DB::beginTransaction();
        try {
            if ($bookmark) {
                // Unbookmark
                $bookmark->delete();
                $bookmarked = false;
                $bookmark = null;
            } else {
                // Bookmark
                $bookmark = Bookmark::create([
                    'user_id' => $userId,
                    'record_type' => $modelClass,
                    'record_id' => $record->id,
                ]);
                $bookmarked = true;
            }

            // Get updated bookmark count
            $count = Bookmark::where('record_type', $modelClass)
                ->where('record_id', $record->id)
                ->count();

            DB::commit();

            return [
                'bookmarked' => $bookmarked,
                'bookmark' => $bookmark,
                'count' => $count,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bookmark toggle failed', [
                'user_id' => $userId,
                'type' => $type,
                'record_id' => $record->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Check if a record is bookmarked by a user
     * 
     * @param int $userId The user ID
     * @param string $type The record type
     * @param string $recordId The record ID (slug/code)
     * @return bool
     */
    public static function isBookmarked(int $userId, string $type, string $recordId): bool
    {
        if (!isset(self::ALLOWED_TYPES[$type])) {
            return false;
        }

        $modelClass = self::ALLOWED_TYPES[$type];

        // Find the record first to get its actual ID
        switch ($type) {
            case 'post':
                $record = Post::where('post_code', $recordId)->first();
                break;
            case 'question':
                $record = Question::where('slug', $recordId)->first();
                break;
            case 'project':
                $record = Project::where('project_code', $recordId)->first();
                break;
            default:
                return false;
        }

        if (!$record) {
            return false;
        }

        return Bookmark::where('user_id', $userId)
            ->where('record_type', $modelClass)
            ->where('record_id', $record->id)
            ->exists();
    }

    /**
     * Get bookmark count for a record
     * 
     * @param string $type The record type
     * @param string $recordId The record ID (slug/code)
     * @return int
     */
    public static function getBookmarkCount(string $type, string $recordId): int
    {
        if (!isset(self::ALLOWED_TYPES[$type])) {
            return 0;
        }

        $modelClass = self::ALLOWED_TYPES[$type];

        // Find the record first to get its actual ID
        switch ($type) {
            case 'post':
                $record = Post::where('post_code', $recordId)->first();
                break;
            case 'question':
                $record = Question::where('slug', $recordId)->first();
                break;
            case 'project':
                $record = Project::where('project_code', $recordId)->first();
                break;
            default:
                return 0;
        }

        if (!$record) {
            return 0;
        }

        return Bookmark::where('record_type', $modelClass)
            ->where('record_id', $record->id)
            ->count();
    }

    /**
     * Get user's bookmarks with pagination
     * 
     * @param int $userId The user ID
     * @param string|null $type Filter by type (optional)
     * @param int $perPage Number of items per page
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getUserBookmarks(int $userId, ?string $type = null, int $perPage = 20): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = Bookmark::with([
            'record' => function ($query) {
                // Eager load relationships based on record type
                $query->with(['user:id,name,username']);
            },
            'user:id,name,username'
        ])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc');

        // Filter by type if provided
        if ($type && isset(self::ALLOWED_TYPES[$type])) {
            $query->where('record_type', self::ALLOWED_TYPES[$type]);
        }

        // Laravel paginator automatically reads 'page' from request
        return $query->paginate($perPage);
    }

    /**
     * Get bookmarks for multiple records
     * 
     * @param int $userId The user ID
     * @param string $type The record type
     * @param array $recordIds Array of record IDs
     * @return array Returns ['record_id' => bool] mapping
     */
    public static function getBookmarksForRecords(int $userId, string $type, array $recordIds): array {
        if (empty($recordIds) || !isset(self::ALLOWED_TYPES[$type])) {
            return [];
        }

        $modelClass = self::ALLOWED_TYPES[$type];

        $bookmarks = Bookmark::where('user_id', $userId)
            ->where('record_type', $modelClass)
            ->whereIn('record_id', $recordIds)
            ->pluck('record_id')
            ->toArray();

        $result = [];
        foreach ($recordIds as $recordId) {
            $result[$recordId] = in_array($recordId, $bookmarks);
        }

        return $result;
    }

    /**
     * Get bookmark counts for multiple records
     * 
     * @param string $type The record type
     * @param array $recordIds Array of record IDs
     * @return array Returns ['record_id' => int] mapping
     */
    public static function getBookmarkCountsForRecords(string $type, array $recordIds): array
    {
        if (empty($recordIds) || !isset(self::ALLOWED_TYPES[$type])) {
            return [];
        }

        $modelClass = self::ALLOWED_TYPES[$type];

        $counts = Bookmark::where('record_type', $modelClass)
            ->whereIn('record_id', $recordIds)
            ->select('record_id', DB::raw('count(*) as count'))
            ->groupBy('record_id')
            ->pluck('count', 'record_id')
            ->toArray();

        $result = [];
        foreach ($recordIds as $recordId) {
            $result[$recordId] = $counts[$recordId] ?? 0;
        }

        return $result;
    }

    /**
     * Remove bookmark
     * 
     * @param int $userId The user ID
     * @param string $type The record type
     * @param int $recordId The record ID
     * @return bool
     */
    public static function remove(int $userId, string $type, int $recordId): bool
    {
        if (!isset(self::ALLOWED_TYPES[$type])) {
            return false;
        }

        $modelClass = self::ALLOWED_TYPES[$type];

        $bookmark = Bookmark::where('user_id', $userId)
            ->where('record_type', $modelClass)
            ->where('record_id', $recordId)
            ->first();

        if ($bookmark) {
            return $bookmark->delete();
        }

        return false;
    }

    /**
     * Get allowed bookmark types
     * 
     * @return array
     */
    public static function getAllowedTypes(): array
    {
        return array_keys(self::ALLOWED_TYPES);
    }
}

