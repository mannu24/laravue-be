<?php

declare(strict_types=1);

namespace App\Services\Gamification;

use App\Enums\TaskFrequency;
use App\Enums\TaskStatus;
use App\Enums\XpEventType;
use App\Models\Answer;
use App\Models\Question;
use App\Models\User;
use App\Models\Task;
use App\Models\UserTask;
use App\Models\XpLog;
use App\Models\ProfileVisit;
use App\Repositories\Gamification\TaskRepository;
use App\Services\Achievements\AchievementsPipeline;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class TaskService
{
    public function __construct(
        protected TaskRepository $taskRepository,
        protected XpService $xpService,
        protected BadgeService $badgeService,
        protected ?AchievementsPipeline $achievements = null
    ) {
    }

    /**
     * Generate daily tasks for user.
     */
    public function generateDailyTasksForUser(User $user): Collection
    {
        $dailyTasks = $this->taskRepository->getDailyTasks();

        foreach ($dailyTasks as $task) {
            // Check if task is already assigned today (pending or completed)
            $existingTask = $user->tasks()
                ->where('task_id', $task->id)
                ->whereDate('assigned_at', today())
                ->first();

            if (!$existingTask) {
                $this->taskRepository->assignToUser($task->id, $user->id);
            }
        }

        $tasks = $this->taskRepository->getTasksForUser($user->id, 'daily');
        $this->autoCompleteTasks($user, $tasks, TaskFrequency::DAILY);

        return $this->taskRepository->getTasksForUser($user->id, 'daily');
    }

    /**
     * Generate weekly tasks for user.
     */
    public function generateWeeklyTasksForUser(User $user): Collection
    {
        $weeklyTasks = $this->taskRepository->getWeeklyTasks();

        foreach ($weeklyTasks as $task) {
            // Check if task is already assigned this week (pending or completed)
            $existingTask = $user->tasks()
                ->where('task_id', $task->id)
                ->whereBetween('assigned_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->first();

            if (!$existingTask) {
                $this->taskRepository->assignToUser($task->id, $user->id);
            }
        }

        $tasks = $this->taskRepository->getTasksForUser($user->id, 'weekly');
        $this->autoCompleteTasks($user, $tasks, TaskFrequency::WEEKLY);

        return $this->taskRepository->getTasksForUser($user->id, 'weekly');
    }

    /**
     * Mark task as completed.
     */
    public function markCompleted(int $taskId, User $user, array $context = []): UserTask
    {
        $task = Task::findOrFail($taskId);
        $completionSource = $context['source'] ?? 'manual';

        // Check if task was already completed today to prevent duplicate completions
        $alreadyCompleted = \App\Models\UserTask::where('user_id', $user->id)
            ->where('task_id', $taskId)
            ->where('status', TaskStatus::COMPLETED->value)
            ->whereDate('completed_at', today())
            ->first();

        if ($alreadyCompleted) {
            // Task already completed today, return existing userTask without broadcasting
            return $alreadyCompleted;
        }

        $userTask = $this->taskRepository->markCompleted($taskId, $user->id);

        // Award XP using task's xp_reward field
        $eventType = $task->frequency === TaskFrequency::DAILY 
            ? XpEventType::DAILY_TASK_COMPLETED->value 
            : XpEventType::WEEKLY_TASK_COMPLETED->value;
        
        $xpAmount = $task->xp_reward ?? 0;
        
        if ($xpAmount > 0) {
            $this->xpService->awardCustomXp(
                $user,
                $eventType,
                $xpAmount,
                [
                    'task_id' => $task->id,
                    'task_title' => $task->title,
                    'frequency' => $task->frequency->value,
                ]
            );
        }

        // Check for task-related badges
        $this->checkTaskRelatedBadges($user, $task);

        // Trigger achievement event (this broadcasts UserCompletedTask)
        if ($this->achievements) {
            $this->achievements->triggerTaskCompleted($user, $task, [
                'source' => $completionSource,
                'xp_reward' => $xpAmount, // Pass the actual XP amount awarded
            ]);
        }

        return $userTask;
    }

    /**
     * Check and award task-related badges.
     */
    protected function checkTaskRelatedBadges(User $user, Task $completedTask): void
    {
        // Check for "Perfect Week" badge - all weekly tasks completed this week
        if ($completedTask->frequency === TaskFrequency::WEEKLY) {
            $this->checkPerfectWeekBadge($user);
        }

        // Check for "Perfect Month" badge - all tasks completed this month
        $this->checkPerfectMonthBadge($user);
    }

    /**
     * Check if user has completed all weekly tasks this week.
     */
    protected function checkPerfectWeekBadge(User $user): void
    {
        $weeklyTasks = $this->taskRepository->getWeeklyTasks();
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();

        // Get all weekly tasks assigned this week
        $assignedWeeklyTasks = $user->tasks()
            ->where('frequency', TaskFrequency::WEEKLY->value)
            ->whereBetween('user_tasks.assigned_at', [$weekStart, $weekEnd])
            ->get();

        // Check if all assigned weekly tasks are completed
        $allCompleted = $assignedWeeklyTasks->every(function ($task) {
            return $task->pivot->status === TaskStatus::COMPLETED->value;
        });

        // Also check if we have all weekly tasks assigned
        $allTasksAssigned = $assignedWeeklyTasks->count() === $weeklyTasks->count();

        if ($allCompleted && $allTasksAssigned && $weeklyTasks->count() > 0) {
            $this->badgeService->checkAndAwardBadge($user, 'perfect-week');
        }
    }

    /**
     * Check if user has completed all tasks this month.
     */
    protected function checkPerfectMonthBadge(User $user): void
    {
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();

        // Get all tasks (daily + weekly) assigned this month
        $assignedTasks = $user->tasks()
            ->whereBetween('user_tasks.assigned_at', [$monthStart, $monthEnd])
            ->get();

        // Check if all assigned tasks are completed
        $allCompleted = $assignedTasks->every(function ($task) {
            return $task->pivot->status === TaskStatus::COMPLETED->value;
        });

        // Get total active tasks count
        $totalActiveTasks = $this->taskRepository->getActiveTasks()->count();

        // Check if we have a reasonable number of tasks assigned (at least 80% of active tasks)
        $tasksAssignedRatio = $totalActiveTasks > 0 
            ? ($assignedTasks->count() / $totalActiveTasks) 
            : 0;

        if ($allCompleted && $tasksAssignedRatio >= 0.8 && $assignedTasks->count() > 0) {
            $this->badgeService->checkAndAwardBadge($user, 'perfect-month');
        }
    }

    /**
     * Get tasks for user.
     */
    public function getTasksForUser(User $user): Collection
    {
        return $this->taskRepository->getTasksForUser($user->id);
    }

    /**
     * Assign task to user.
     */
    public function assignTask(int $taskId, User $user): UserTask
    {
        return $this->taskRepository->assignToUser($taskId, $user->id);
    }

    /**
     * Attempt to auto-complete tasks that meet their conditions.
     */
    protected function autoCompleteTasks(User $user, EloquentCollection $tasks, TaskFrequency $frequency): void
    {
        if ($tasks->isEmpty()) {
            return;
        }

        $stats = $this->getAutoCompletionStats($user);

        foreach ($tasks as $task) {
            $pivotStatus = $task->pivot->status ?? null;

            // Skip if already completed
            if ($pivotStatus === TaskStatus::COMPLETED->value) {
                continue;
            }

            // Double-check: Verify task is actually pending in database before attempting completion
            $userTask = $user->tasks()
                ->where('task_id', $task->id)
                ->where('status', TaskStatus::PENDING->value)
                ->first();

            if (!$userTask) {
                // Task is not pending, skip
                continue;
            }

            if ($this->shouldAutoCompleteTask($task, $stats)) {
                try {
                    $this->markCompleted($task->id, $user, ['source' => 'auto']);
                } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
                    // Task already completed or not found, skip silently
                    logger()->debug('[TaskService] Task already completed or not found', [
                        'task_id' => $task->id,
                        'user_id' => $user->id,
                    ]);
                } catch (\Throwable $exception) {
                    logger()->debug('[TaskService] Auto-completion failed', [
                        'task_id' => $task->id,
                        'user_id' => $user->id,
                        'message' => $exception->getMessage(),
                    ]);
                }
            }
        }
    }

    /**
     * Determine if a task qualifies for auto completion.
     */
    protected function shouldAutoCompleteTask(Task $task, array $stats): bool
    {
        $title = strtolower($task->title);

        return match ($title) {
            'login today' => $stats['logged_in_today'],
            'earn 20 xp' => $stats['xp_today'] >= 20,
            'answer 1 question' => $stats['answers_today'] >= 1,
            'ask 1 question' => $stats['questions_today'] >= 1,
            'complete a task' => $stats['daily_tasks_completed_today'] >= 1,
            'visit someone\'s profile' => $stats['profile_visits_today'] >= 1,

            // Weekly tasks
            'earn 200 xp' => $stats['xp_week'] >= 200,
            'complete 10 daily tasks' => $stats['daily_tasks_completed_week'] >= 10,
            'answer 5 questions' => $stats['answers_week'] >= 5,
            'ask 3 questions' => $stats['questions_week'] >= 3,
            'get 1 verified answer' => $stats['verified_answers_week'] >= 1,
            'maintain 7-day streak' => $stats['streak_days'] >= 7,
            'visit 10 profiles' => $stats['profile_visits_week'] >= 10,
            default => false,
        };
    }

    /**
     * Build metrics needed for auto-completion checks.
     */
    protected function getAutoCompletionStats(User $user): array
    {
        $today = Carbon::today();
        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd = Carbon::now()->endOfWeek();

        $stats = [
            'logged_in_today' => $user->last_active_at?->isToday() ?? false,
            'xp_today' => XpLog::where('user_id', $user->id)
                ->whereDate('created_at', $today)
                ->sum('xp_amount'),
            'xp_week' => XpLog::where('user_id', $user->id)
                ->whereBetween('created_at', [$weekStart, $weekEnd])
                ->sum('xp_amount'),
            'answers_today' => Answer::where('user_id', $user->id)
                ->whereDate('created_at', $today)
                ->count(),
            'answers_week' => Answer::where('user_id', $user->id)
                ->whereBetween('created_at', [$weekStart, $weekEnd])
                ->count(),
            'questions_today' => Question::where('user_id', $user->id)
                ->whereDate('created_at', $today)
                ->count(),
            'questions_week' => Question::where('user_id', $user->id)
                ->whereBetween('created_at', [$weekStart, $weekEnd])
                ->count(),
            'daily_tasks_completed_today' => UserTask::where('user_id', $user->id)
                ->where('status', TaskStatus::COMPLETED->value)
                ->whereDate('completed_at', $today)
                ->whereHas('task', fn ($query) => $query->where('frequency', TaskFrequency::DAILY->value))
                ->count(),
            'daily_tasks_completed_week' => UserTask::where('user_id', $user->id)
                ->where('status', TaskStatus::COMPLETED->value)
                ->whereBetween('completed_at', [$weekStart, $weekEnd])
                ->whereHas('task', fn ($query) => $query->where('frequency', TaskFrequency::DAILY->value))
                ->count(),
            'verified_answers_week' => Answer::where('user_id', $user->id)
                ->where('is_verified', true)
                ->whereBetween('updated_at', [$weekStart, $weekEnd])
                ->count(),
            'streak_days' => $user->streak_days ?? 0,
            'profile_visits_today' => (int) \DB::table('profile_visits')
                ->where('visitor_id', $user->id)
                ->whereDate('visited_at', $today)
                ->select(\DB::raw('COUNT(DISTINCT visited_user_id) as count'))
                ->value('count') ?? 0,
            'profile_visits_week' => (int) \DB::table('profile_visits')
                ->where('visitor_id', $user->id)
                ->whereBetween('visited_at', [$weekStart, $weekEnd])
                ->select(\DB::raw('COUNT(DISTINCT visited_user_id) as count'))
                ->value('count') ?? 0,
        ];

        return $stats;
    }

    /**
     * Complete task by title (for automatic detection).
     */
    public function completeByTitle(string $taskTitle, User $user): ?UserTask
    {
        $task = $this->taskRepository->findByTitle($taskTitle);
        
        if (!$task) {
            return null;
        }

        // Check if task is already assigned and pending
        $userTask = $user->tasks()
            ->where('task_id', $task->id)
            ->where('status', TaskStatus::PENDING->value)
            ->first();

        if (!$userTask) {
            // Task not assigned yet, assign it first
            $userTask = $this->taskRepository->assignToUser($task->id, $user->id);
        }

        // Complete the task
        return $this->markCompleted($task->id, $user, ['source' => 'auto_route']);
    }
}

