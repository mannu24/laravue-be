<?php

declare(strict_types=1);

namespace App\Repositories\Gamification;

use App\Enums\TaskFrequency;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use App\Models\UserTask;
use Illuminate\Database\Eloquent\Collection;

class TaskRepository
{
    public function __construct(protected Task $model)
    {
    }

    /**
     * Get all active tasks.
     */
    public function getActiveTasks(): Collection
    {
        return $this->model->where('is_active', true)
            ->orderBy('frequency', 'asc')
            ->orderBy('title', 'asc')
            ->get();
    }

    /**
     * Get daily tasks.
     */
    public function getDailyTasks(): Collection
    {
        return $this->model->where('is_active', true)
            ->where('frequency', TaskFrequency::DAILY->value)
            ->orderBy('title', 'asc')
            ->get();
    }

    /**
     * Get weekly tasks.
     */
    public function getWeeklyTasks(): Collection
    {
        return $this->model->where('is_active', true)
            ->where('frequency', TaskFrequency::WEEKLY->value)
            ->orderBy('title', 'asc')
            ->get();
    }

    /**
     * Assign task to user.
     */
    public function assignToUser(int $taskId, int $userId): UserTask
    {
        $task = $this->model->findOrFail($taskId);
        $user = User::findOrFail($userId);

        // Check if already assigned
        $userTask = UserTask::where('user_id', $userId)
            ->where('task_id', $taskId)
            ->where('status', TaskStatus::PENDING->value)
            ->first();

        if ($userTask) {
            return $userTask;
        }

        return UserTask::create([
            'user_id' => $userId,
            'task_id' => $taskId,
            'assigned_at' => now(),
            'status' => TaskStatus::PENDING->value,
        ]);
    }

    /**
     * Mark task as completed for user.
     */
    public function markCompleted(int $taskId, int $userId): UserTask
    {
        $userTask = UserTask::where('user_id', $userId)
            ->where('task_id', $taskId)
            ->where('status', TaskStatus::PENDING->value)
            ->firstOrFail();

        $userTask->update([
            'status' => TaskStatus::COMPLETED->value,
            'completed_at' => now(),
        ]);

        return $userTask->fresh();
    }

    /**
     * Get tasks for a user.
     */
    public function getTasksForUser(int $userId): Collection
    {
        $user = User::findOrFail($userId);
        return $user->tasks()
            ->withPivot('status', 'completed_at', 'assigned_at')
            ->orderBy('user_tasks.assigned_at', 'desc')
            ->get();
    }
}

