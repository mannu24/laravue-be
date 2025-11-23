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
     * Find task by title.
     */
    public function findByTitle(string $title): ?Task
    {
        return $this->model->where('title', $title)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Assign task to user.
     */
    public function assignToUser(int $taskId, int $userId): UserTask
    {
        $task = $this->model->findOrFail($taskId);
        $user = User::findOrFail($userId);

        // Check if task is already assigned based on frequency
        $query = UserTask::where('user_id', $userId)
            ->where('task_id', $taskId);

        // For daily tasks, check if assigned today
        if ($task->frequency === TaskFrequency::DAILY->value) {
            $query->whereDate('assigned_at', today());
        }
        // For weekly tasks, check if assigned this week
        elseif ($task->frequency === TaskFrequency::WEEKLY->value) {
            $query->whereBetween('assigned_at', [now()->startOfWeek(), now()->endOfWeek()]);
        }
        // For other frequencies, check if any pending exists
        else {
            $query->where('status', TaskStatus::PENDING->value);
        }

        $existingTask = $query->first();

        if ($existingTask) {
            return $existingTask;
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
        // First, try to find a pending task
        $userTask = UserTask::where('user_id', $userId)
            ->where('task_id', $taskId)
            ->where('status', TaskStatus::PENDING->value)
            ->first();

        // If no pending task found, check if already completed (to prevent duplicate completions)
        if (!$userTask) {
            $completedTask = UserTask::where('user_id', $userId)
                ->where('task_id', $taskId)
                ->where('status', TaskStatus::COMPLETED->value)
                ->whereDate('completed_at', today())
                ->first();

            if ($completedTask) {
                // Task already completed today, return it without updating
                return $completedTask;
            }

            // No pending or completed task found, throw exception
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException(
                "No pending task found for task_id: {$taskId} and user_id: {$userId}"
            );
        }

        $userTask->update([
            'status' => TaskStatus::COMPLETED->value,
            'completed_at' => now(),
        ]);

        return $userTask->fresh();
    }

    /**
     * Get tasks for a user.
     * Returns only the most recent task assignment for each task (today for daily, this week for weekly).
     */
    public function getTasksForUser(int $userId, string $frequency = null): Collection
    {
        $user = User::findOrFail($userId);
        
        // Get all user tasks with pivot data
        $query = $user->tasks();
        if ($frequency) {
            $query->where('frequency', $frequency);
        }
        
        $allTasks = $query->withPivot('status', 'completed_at', 'assigned_at')
            ->orderBy('user_tasks.assigned_at', 'desc')
            ->get();
        
        // Group by task_id and keep only one task per task_id
        // Priority: pending tasks first, then most recent
        // For daily tasks, only show tasks assigned today
        // For weekly tasks, only show tasks assigned this week
        $filteredItems = [];
        
        foreach ($allTasks->groupBy('id') as $taskGroup) {
            // For daily tasks, prefer completed tasks assigned today over pending tasks
            // For other cases, prefer pending tasks, then most recent
            if ($frequency === 'daily') {
                // Filter to only tasks assigned today
                $todayTasks = $taskGroup->filter(function ($task) {
                    if (!$task->pivot->assigned_at) {
                        return false;
                    }
                    $assignedAt = \Illuminate\Support\Carbon::parse($task->pivot->assigned_at);
                    return $assignedAt->isToday();
                });
                
                if ($todayTasks->isEmpty()) {
                    continue;
                }
                
                // Prefer completed tasks assigned today, then pending
                $completedToday = $todayTasks->firstWhere('pivot.status', TaskStatus::COMPLETED->value);
                $mostRelevant = $completedToday ?: $todayTasks->firstWhere('pivot.status', TaskStatus::PENDING->value);
                
                if (!$mostRelevant) {
                    $mostRelevant = $todayTasks->first();
                }
            } else if ($frequency === 'weekly') {
                // Filter to only tasks assigned this week
                $startOfWeek = now()->startOfWeek();
                $endOfWeek = now()->endOfWeek();
                
                $weekTasks = $taskGroup->filter(function ($task) use ($startOfWeek, $endOfWeek) {
                    if (!$task->pivot->assigned_at) {
                        return false;
                    }
                    $assignedAt = \Illuminate\Support\Carbon::parse($task->pivot->assigned_at);
                    return $assignedAt->between($startOfWeek, $endOfWeek);
                });
                
                if ($weekTasks->isEmpty()) {
                    continue;
                }
                
                // Prefer completed tasks assigned this week, then pending
                $completedThisWeek = $weekTasks->firstWhere('pivot.status', TaskStatus::COMPLETED->value);
                $mostRelevant = $completedThisWeek ?: $weekTasks->firstWhere('pivot.status', TaskStatus::PENDING->value);
                
                if (!$mostRelevant) {
                    $mostRelevant = $weekTasks->first();
                }
            } else {
                // For all tasks (no frequency filter), prefer pending tasks, then most recent
                $pending = $taskGroup->firstWhere('pivot.status', TaskStatus::PENDING->value);
                $mostRelevant = $pending ?: $taskGroup->first();
                
                if (!$mostRelevant->pivot->assigned_at) {
                    $filteredItems[] = $mostRelevant;
                    continue;
                }
            }
            
            $filteredItems[] = $mostRelevant;
        }
        
        // Return as Eloquent Collection (preserves pivot data and relationships)
        return \Illuminate\Database\Eloquent\Collection::make($filteredItems);
    }
}

