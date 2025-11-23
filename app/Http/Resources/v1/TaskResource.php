<?php

declare(strict_types=1);

namespace App\Http\Resources\v1;

use App\Models\Answer;
use App\Models\Question;
use App\Models\ProfileVisit;
use App\Models\User;
use App\Models\UserTask;
use App\Enums\TaskFrequency;
use App\Enums\TaskStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use App\Models\XpLog;

class TaskResource extends JsonResource
{
    /**
     * Static property to hold user context for progress calculation.
     */
    protected static ?User $contextUser = null;

    /**
     * Set the user context for progress calculation.
     */
    public static function setContextUser(?User $user): void
    {
        static::$contextUser = $user;
    }

    /**
     * Get the user context.
     */
    protected static function getContextUser(Request $request): ?User
    {
        return static::$contextUser 
            ?? $request->route('user') 
            ?? $request->user();
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Get user from static context, route parameter, or authenticated user
        $user = static::getContextUser($request);
        $progress = null;

        // Calculate progress for weekly tasks
        // Note: $this->frequency is a TaskFrequency enum instance (not a string) due to model casting
        if ($this->frequency === TaskFrequency::WEEKLY && $user && isset($this->pivot)) {
            $progress = $this->calculateProgress($user);
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'frequency' => $this->frequency,
            'xp_reward' => $this->xp_reward ?? 0,
            'status' => $this->when(isset($this->pivot), fn() => $this->pivot->status),
            'completed_at' => $this->when(isset($this->pivot), function () {
                if (!$this->pivot->completed_at) {
                    return null;
                }
                return \Illuminate\Support\Carbon::parse($this->pivot->completed_at)->toDateTimeString();
            }),
            'assigned_at' => $this->when(isset($this->pivot), function () {
                if (!$this->pivot->assigned_at) {
                    return null;
                }
                return \Illuminate\Support\Carbon::parse($this->pivot->assigned_at)->toDateTimeString();
            }),
            'progress' => $progress,
        ];
    }

    /**
     * Calculate progress for weekly tasks based on task title.
     */
    protected function calculateProgress($user): ?array
    {
        $title = $this->title;
        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd = Carbon::now()->endOfWeek();

        // Extract target number from title (e.g., "Answer 5 Questions" -> 5)
        if (preg_match('/(\d+)/', $title, $matches)) {
            $target = (int) $matches[1];
        } else {
            // For tasks without numbers (like "Get 1 Verified Answer"), default to 1
            $target = 1;
        }

        $current = 0;

        // Map task titles to their progress calculation
        if (str_contains($title, 'Answer') && str_contains($title, 'Question')) {
            $current = Answer::where('user_id', $user->id)
                ->whereBetween('created_at', [$weekStart, $weekEnd])
                ->count();
        } elseif (str_contains($title, 'Ask') && str_contains($title, 'Question')) {
            $current = Question::where('user_id', $user->id)
                ->whereBetween('created_at', [$weekStart, $weekEnd])
                ->count();
        } elseif (str_contains($title, 'Complete') && str_contains($title, 'Daily Task')) {
            $current = UserTask::where('user_id', $user->id)
                ->where('status', TaskStatus::COMPLETED->value)
                ->whereBetween('completed_at', [$weekStart, $weekEnd])
                ->whereHas('task', fn ($query) => $query->where('frequency', TaskFrequency::DAILY->value))
                ->count();
        } elseif (str_contains($title, 'Verified Answer')) {
            $current = Answer::where('user_id', $user->id)
                ->where('is_verified', true)
                ->whereBetween('updated_at', [$weekStart, $weekEnd])
                ->count();
        } elseif (str_contains($title, 'Earn') && str_contains($title, 'XP')) {
            $current = (int) XpLog::where('user_id', $user->id)
                ->whereBetween('created_at', [$weekStart, $weekEnd])
                ->sum('xp_amount');
        } elseif (str_contains($title, 'Streak')) {
            $current = min($user->streak_days ?? 0, 7); // Cap at 7 for weekly
        } elseif (str_contains($title, 'Visit') && str_contains($title, 'Profile')) {
            $current = ProfileVisit::where('visitor_id', $user->id)
                ->whereBetween('visited_at', [$weekStart, $weekEnd])
                ->distinct('visited_user_id')
                ->count('visited_user_id');
        }

        return [
            'current' => $current,
            'target' => $target,
            'percentage' => $target > 0 ? min(100, round(($current / $target) * 100)) : 0,
        ];
    }
}

