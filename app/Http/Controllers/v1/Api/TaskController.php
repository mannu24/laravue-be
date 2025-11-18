<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Gamification\AssignTaskRequest;
use App\Http\Requests\Gamification\CompleteTaskRequest;
use App\Http\Resources\v1\TaskResource;
use App\Http\Traits\HttpResponse;
use App\Models\User;
use App\Services\Gamification\TaskService;
use Illuminate\Http\JsonResponse;

class TaskController extends Controller
{
    use HttpResponse;

    public function __construct(
        protected TaskService $taskService
    ) {
    }

    /**
     * Get daily tasks for a user.
     */
    public function daily(User $user): JsonResponse
    {
        $tasks = $this->taskService->generateDailyTasksForUser($user);
        
        return $this->success(
            data: TaskResource::collection($tasks),
            message: 'Daily tasks retrieved successfully'
        );
    }

    /**
     * Get weekly tasks for a user.
     */
    public function weekly(User $user): JsonResponse
    {
        $tasks = $this->taskService->generateWeeklyTasksForUser($user);
        
        return $this->success(
            data: TaskResource::collection($tasks),
            message: 'Weekly tasks retrieved successfully'
        );
    }

    /**
     * Mark task as completed.
     */
    public function complete(CompleteTaskRequest $request): JsonResponse
    {
        $user = User::findOrFail($request->user_id);
        $userTask = $this->taskService->markCompleted($request->task_id, $user);
        
        return $this->success(
            data: new TaskResource($userTask->task),
            message: 'Task completed successfully'
        );
    }

    /**
     * Assign task to user.
     */
    public function assign(AssignTaskRequest $request): JsonResponse
    {
        $user = User::findOrFail($request->user_id);
        $userTask = $this->taskService->assignTask($request->task_id, $user);
        
        return $this->success(
            data: new TaskResource($userTask->task),
            message: 'Task assigned successfully'
        );
    }
}
