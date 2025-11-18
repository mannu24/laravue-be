<?php

declare(strict_types=1);

namespace App\Services\Gamification;

use App\Enums\TaskStatus;
use App\Models\User;
use App\Models\Task;
use App\Models\UserTask;
use App\Repositories\Gamification\TaskRepository;
use App\Services\Achievements\AchievementsPipeline;
use Illuminate\Support\Collection;

class TaskService
{
    public function __construct(
        protected TaskRepository $taskRepository,
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
            // Check if task is already assigned today
            $existingTask = $user->tasks()
                ->where('task_id', $task->id)
                ->where('status', TaskStatus::PENDING->value)
                ->whereDate('assigned_at', today())
                ->first();

            if (!$existingTask) {
                $this->taskRepository->assignToUser($task->id, $user->id);
            }
        }

        return $this->taskRepository->getTasksForUser($user->id);
    }

    /**
     * Generate weekly tasks for user.
     */
    public function generateWeeklyTasksForUser(User $user): Collection
    {
        $weeklyTasks = $this->taskRepository->getWeeklyTasks();

        foreach ($weeklyTasks as $task) {
            // Check if task is already assigned this week
            $existingTask = $user->tasks()
                ->where('task_id', $task->id)
                ->where('status', TaskStatus::PENDING->value)
                ->whereBetween('assigned_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->first();

            if (!$existingTask) {
                $this->taskRepository->assignToUser($task->id, $user->id);
            }
        }

        return $this->taskRepository->getTasksForUser($user->id);
    }

    /**
     * Mark task as completed.
     */
    public function markCompleted(int $taskId, User $user): UserTask
    {
        $userTask = $this->taskRepository->markCompleted($taskId, $user->id);
        $task = Task::findOrFail($taskId);

        // Trigger achievement event
        if ($this->achievements) {
            $this->achievements->triggerTaskCompleted($user, $task);
        }

        return $userTask;
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
}

