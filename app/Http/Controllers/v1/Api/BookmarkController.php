<?php

namespace App\Http\Controllers\v1\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\HttpResponse;
use App\Services\BookmarkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

class BookmarkController extends Controller
{
    use HttpResponse;

    /**
     * Toggle bookmark status for a record
     * Single API endpoint that handles both bookmark and unbookmark
     * 
     * POST /api/v1/bookmarks/toggle
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function toggle(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'type' => 'required|string|in:post,question,project',
                'record_id' => 'required|string',
            ]);

            if ($validator->fails()) {
                return $this->error(
                    data: $validator->errors(),
                    message: 'Validation failed',
                    code: 422
                );
            }

            $authUser = auth()->guard('api')->user();

            if (!$authUser) {
                return $this->error('Unauthorized', 401);
            }

            $type = $request->input('type');
            $recordId = $request->input('record_id');

            $result = BookmarkService::toggle($authUser->id, $type, $recordId);

            return $this->success(
                data: [
                    'bookmarked' => $result['bookmarked'],
                    'bookmark_id' => $result['bookmark']?->id,
                    'bookmark_count' => $result['count'],
                ],
                message: $result['bookmarked'] 
                    ? 'Record bookmarked successfully' 
                    : 'Record unbookmarked successfully'
            );
        } catch (InvalidArgumentException $e) {
            return $this->error(
                message: $e->getMessage(),
                code: 404
            );
        } catch (\Exception $e) {
            return $this->internalError(
                message: 'Failed to toggle bookmark'
            );
        }
    }

    /**
     * Check if a record is bookmarked by the authenticated user
     * 
     * GET /api/v1/bookmarks/check
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function check(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'type' => 'required|string|in:post,question,project',
                'record_id' => 'required|string',
            ]);

            if ($validator->fails()) {
                return $this->error(
                    data: $validator->errors(),
                    message: 'Validation failed',
                    code: 422
                );
            }

            $authUser = auth()->guard('api')->user();

            if (!$authUser) {
                return $this->error('Unauthorized', 401);
            }

            $type = $request->input('type');
            $recordId = $request->input('record_id');

            $isBookmarked = BookmarkService::isBookmarked($authUser->id, $type, $recordId);
            $count = BookmarkService::getBookmarkCount($type, $recordId);

            return $this->success(
                data: [
                    'bookmarked' => $isBookmarked,
                    'bookmark_count' => $count,
                ],
                message: 'Bookmark status retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->internalError(
                message: 'Failed to check bookmark status'
            );
        }
    }

    /**
     * Get user's bookmarks with pagination
     * 
     * GET /api/v1/bookmarks
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $authUser = auth()->guard('api')->user();

            if (!$authUser) {
                return $this->error('Unauthorized', 401);
            }

            $validator = Validator::make($request->all(), [
                'type' => 'nullable|string|in:post,question,project',
                'per_page' => 'nullable|integer|min:1|max:50',
                'page' => 'nullable|integer|min:1',
            ]);

            if ($validator->fails()) {
                return $this->error(
                    data: $validator->errors(),
                    message: 'Validation failed',
                    code: 422
                );
            }

            $type = $request->input('type');
            $perPage = min((int) $request->input('per_page', 20), 50);

            // Laravel paginator automatically reads 'page' from request
            $bookmarks = BookmarkService::getUserBookmarks($authUser->id, $type, $perPage);

            // Transform bookmarks for response
            $transformedBookmarks = $bookmarks->map(function ($bookmark) {
                $record = $bookmark->record;
                
                if (!$record) {
                    return null;
                }

                $recordId = $record->id ?? null;
                if (!$recordId) {
                    return null;
                }

                $baseData = [
                    'id' => $bookmark->id,
                    'type' => $this->getTypeFromModel(get_class($record)),
                    'record_id' => $recordId,
                    'bookmarked_at' => $bookmark->created_at->toIso8601String(),
                ];

                // Add type-specific data
                $userData = null;
                if ($record->user && isset($record->user->id)) {
                    $userData = [
                        'id' => $record->user->id,
                        'name' => $record->user->name ?? '',
                        'username' => $record->user->username ?? '',
                        // 'profile_photo' => $record->user->profile_photo ?? '',
                    ];
                }

                if ($record instanceof \App\Models\Post) {
                    $baseData['record'] = [
                        'id' => $recordId,
                        'post_code' => $record->post_code ?? (string) $recordId,
                        'title' => $record->title ?? '',
                        'content' => $record->content ?? '',
                        'user' => $userData,
                    ];
                } elseif ($record instanceof \App\Models\Question) {
                    $baseData['record'] = [
                        'id' => $recordId,
                        'slug' => $record->slug ?? (string) $recordId,
                        'title' => $record->title ?? '',
                        'content' => $record->content ?? '',
                        'user' => $userData,
                    ];
                } elseif ($record instanceof \App\Models\Project) {
                    $baseData['record'] = [
                        'id' => $recordId,
                        'slug' => $record->slug ?? (string) $recordId,
                        'title' => $record->title ?? '',
                        'description' => $record->description ?? '',
                        'user' => $userData,
                    ];
                }

                return $baseData;
            })->filter();

            return $this->success(
                data: [
                    'bookmarks' => $transformedBookmarks->values(),
                    'pagination' => [
                        'current_page' => $bookmarks->currentPage(),
                        'per_page' => $bookmarks->perPage(),
                        'total' => $bookmarks->total(),
                        'last_page' => $bookmarks->lastPage(),
                    ],
                ],
                message: 'Bookmarks retrieved successfully'
            );
        } catch (\Exception $e) {
            dd($e->getTrace());
            return $this->internalError(
                message: 'Failed to retrieve bookmarks'
            );
        }
    }

    /**
     * Get bookmark status for multiple records
     * 
     * POST /api/v1/bookmarks/batch-check
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function batchCheck(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'type' => 'required|string|in:post,question,project',
                'record_ids' => 'required|array|min:1|max:100',
                'record_ids.*' => 'string',
            ]);

            if ($validator->fails()) {
                return $this->error(
                    data: $validator->errors(),
                    message: 'Validation failed',
                    code: 422
                );
            }

            $authUser = auth()->guard('api')->user();

            if (!$authUser) {
                return $this->error('Unauthorized', 401);
            }

            $type = $request->input('type');
            $recordIds = $request->input('record_ids');

            $bookmarks = BookmarkService::getBookmarksForRecords($authUser->id, $type, $recordIds);
            $counts = BookmarkService::getBookmarkCountsForRecords($type, $recordIds);

            return $this->success(
                data: [
                    'bookmarks' => $bookmarks,
                    'counts' => $counts,
                ],
                message: 'Bookmark status retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->internalError(
                message: 'Failed to check bookmark status'
            );
        }
    }

    /**
     * Remove a bookmark
     * 
     * DELETE /api/v1/bookmarks/{id}
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $authUser = auth()->guard('api')->user();

            if (!$authUser) {
                return $this->error('Unauthorized', 401);
            }

            $bookmark = \App\Models\Bookmark::where('id', $id)
                ->where('user_id', $authUser->id)
                ->first();

            if (!$bookmark) {
                return $this->error('Bookmark not found', 404);
            }

            $bookmark->delete();

            return $this->success(
                message: 'Bookmark removed successfully'
            );
        } catch (\Exception $e) {
            return $this->internalError(
                message: 'Failed to remove bookmark'
            );
        }
    }

    /**
     * Get bookmark count for a record
     * 
     * GET /api/v1/bookmarks/count
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function count(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'type' => 'required|string|in:post,question,project',
                'record_id' => 'required|string',
            ]);

            if ($validator->fails()) {
                return $this->error(
                    data: $validator->errors(),
                    message: 'Validation failed',
                    code: 422
                );
            }

            $type = $request->input('type');
            $recordId = $request->input('record_id');

            $count = BookmarkService::getBookmarkCount($type, $recordId);

            return $this->success(
                data: [
                    'bookmark_count' => $count,
                ],
                message: 'Bookmark count retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->internalError(
                message: 'Failed to get bookmark count'
            );
        }
    }

    /**
     * Helper method to get type string from model class
     * 
     * @param string $modelClass
     * @return string
     */
    private function getTypeFromModel(string $modelClass): string
    {
        $mapping = [
            \App\Models\Post::class => 'post',
            \App\Models\Question::class => 'question',
            \App\Models\Project::class => 'project',
        ];

        return $mapping[$modelClass] ?? 'unknown';
    }
}
