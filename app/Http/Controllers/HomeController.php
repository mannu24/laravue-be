<?php

namespace App\Http\Controllers;

use App\Enums\TaskStatus;
use App\Http\Resources\v1\BadgeResource;
use App\Http\Resources\v1\LevelResource;
use App\Http\Resources\v1\TaskResource;
use App\Http\Resources\v1\UserResource;
use App\Http\Resources\v1\XpResource;
use App\Http\Traits\HttpResponse;
use App\Models\UserTask;
use App\Services\Gamification\BadgeService;
use App\Services\Gamification\TaskService;
use App\Services\Gamification\XpService;
use App\Services\User\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    use HttpResponse;

    public function __construct(
        protected UserService $userService,
        protected XpService $xpService,
        protected BadgeService $badgeService,
        protected TaskService $taskService
    ) {
    }

    public function index(): View
    {
        return view('vue');
    }

    public function globalData(Request $request): JsonResponse
    {
        $user = $request->user()->load(['level', 'socialLinks.socialLinkType']);
        $gamification = $this->userService->getUserWithGamification($user->id);
        $xpSummary = $this->xpService->getUserXpSummary($user);
        $allBadges = $this->badgeService->getAllBadges();
        $earnedBadges = $this->badgeService->getBadgesForUser($user->id);
        $dailyTasks = $this->taskService->generateDailyTasksForUser($user);
        $weeklyTasks = $this->taskService->generateWeeklyTasksForUser($user);
        $completedTasksCount = UserTask::where('user_id', $user->id)
            ->where('status', TaskStatus::COMPLETED->value)
            ->count();

        // Pass user to resource collection via static context
        TaskResource::setContextUser($user);

        return $this->success(
            data: [
                'user' => new UserResource($user),
                'gamification' => [
                    'summary' => [
                        'xp_total' => $gamification['xp_total'],
                        'streak_days' => $gamification['streak_days'],
                        'badges_count' => $gamification['badges']->count(),
                        'tasks_completed' => $completedTasksCount,
                    ],
                    'level' => $gamification['level'] ? new LevelResource($gamification['level']) : null,
                    // 'badges' => BadgeResource::collection($gamification['badges']),
                    // 'tasks' => TaskResource::collection($gamification['tasks']),
                    // 'recent_xp_logs' => XpResource::collection($gamification['recent_xp_logs']),
                ],
                'xp_summary' => [
                    'total_xp' => $xpSummary['total_xp'] ?? 0,
                    'current_level' => $xpSummary['current_level'] ? new LevelResource($xpSummary['current_level']) : null,
                    'next_level' => $xpSummary['next_level'] ? new LevelResource($xpSummary['next_level']) : null,
                    'xp_to_next_level' => $xpSummary['xp_to_next_level'] ?? 0,
                ],
                'badges' => [
                    'all' => BadgeResource::collection($allBadges),
                    'earned' => BadgeResource::collection($earnedBadges),
                ],
                'tasks' => [
                    'daily' => TaskResource::collection($dailyTasks),
                    'weekly' => TaskResource::collection($weeklyTasks),
                ],
            ],
            message: 'Global data retrieved successfully'
        );
    }
}
