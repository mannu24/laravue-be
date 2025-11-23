<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1\Api;

use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateProfileRequest;
use App\Http\Resources\v1\BadgeResource;
use App\Http\Resources\v1\LevelResource;
use App\Http\Resources\v1\TaskResource;
use App\Http\Resources\v1\UserResource;
use App\Http\Resources\v1\XpResource;
use App\Http\Traits\HttpResponse;
use App\Models\UserTask;
use App\Services\Gamification\XpService;
use App\Services\User\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use HttpResponse;

    public function __construct(
        protected UserService $userService,
        protected XpService $xpService
    ) {
    }

    /**
     * Show user profile.
     */
    public function show(Request $request, string $username): JsonResponse
    {
        // Remove @ prefix if present
        $username = ltrim($username, '@');
        
        $userProfile = $this->userService->getUserProfile($username);
        
        if (!$userProfile) {
            abort(404, 'User not found');
        }
        
        // Track profile visit if authenticated user is visiting someone else's profile
        // Note: Route is public, so user() may be null if auth token not provided
        $authenticatedUser = auth()->guard('api')->user();
        
        if ($authenticatedUser && $authenticatedUser->id !== $userProfile->id) {
            try {
                $this->userService->trackProfileVisit($authenticatedUser->id, $userProfile->id);
            } catch (\Exception $e) {
                // Log error but don't fail the request
                logger()->error('Failed to track profile visit', [
                    'visitor_id' => $authenticatedUser->id,
                    'visited_user_id' => $userProfile->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        // Get gamification data
        $gamification = $this->userService->getUserWithGamification($userProfile->id);
        $xpSummary = $this->xpService->getUserXpSummary($userProfile);
        $completedTasksCount = UserTask::where('user_id', $userProfile->id)
            ->where('status', TaskStatus::COMPLETED->value)
            ->count();
        
        // Pass user to resource collection via static context (for tasks in UserResource)
        TaskResource::setContextUser($userProfile);
        
        return $this->success(
            data: [
                'user' => new UserResource($userProfile->load(['level', 'badges', 'tasks', 'socialLinks.socialLinkType'])),
                'gamification' => [
                    'summary' => [
                        'xp_total' => $gamification['xp_total'],
                        'streak_days' => $gamification['streak_days'],
                        'badges_count' => $gamification['badges']->count(),
                        'tasks_completed' => $completedTasksCount,
                    ],
                    'level' => $gamification['level'] ? new LevelResource($gamification['level']) : null,
                ],
                'xp_summary' => [
                    'total_xp' => $xpSummary['total_xp'] ?? 0,
                    'current_level' => $xpSummary['current_level'] ? new LevelResource($xpSummary['current_level']) : null,
                    'next_level' => $xpSummary['next_level'] ? new LevelResource($xpSummary['next_level']) : null,
                    'xp_to_next_level' => $xpSummary['xp_to_next_level'] ?? 0,
                ],
            ],
            message: 'User profile retrieved successfully'
        );
    }

    /**
     * Get the authenticated user's profile.
     */
    public function user(Request $request): JsonResponse
    {
        $user = $request->user()->load(['level', 'badges', 'tasks', 'socialLinks.socialLinkType']);

        // Pass user to resource collection via static context (for tasks in UserResource)
        TaskResource::setContextUser($user);

        return $this->success(
            data: ['user' => new UserResource($user)],
            message: 'Authenticated user retrieved successfully'
        );
    }

    /**
     * Update user profile.
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $updatedUser = $this->userService->updateProfile($user, $request->validated(), $request);
        
        return $this->success(
            data: new UserResource($updatedUser->load('level')),
            message: 'Profile updated successfully'
        );
    }

    /**
     * Get user gamification data.
     */
    public function gamification(User $user): JsonResponse
    {
        $data = $this->userService->getUserWithGamification($user->id);
        
        // Pass user to resource collection via static context
        TaskResource::setContextUser($user);
        
        return $this->success(
            data: [
                'user' => new UserResource($data['user']),
                'xp_total' => $data['xp_total'],
                'level' => $data['level'] ? new LevelResource($data['level']) : null,
                'badges' => BadgeResource::collection($data['badges']),
                'tasks' => TaskResource::collection($data['tasks']),
                'recent_xp_logs' => XpResource::collection($data['recent_xp_logs']),
            ],
            message: 'Gamification data retrieved successfully'
        );
    }
}

