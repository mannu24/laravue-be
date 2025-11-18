<?php

declare(strict_types=1);

namespace App\Services\Achievements;

use App\Events\Gamification\UserEarnedXp;
use App\Events\Gamification\UserLeveledUp;
use App\Events\Gamification\UserUnlockedBadge;
use App\Events\Gamification\UserCompletedTask;
use App\Events\Gamification\UserAnsweredVerified;
use App\Models\Answer;
use App\Models\Badge;
use App\Models\Level;
use App\Models\Task;
use App\Models\User;
use App\Models\XpLog;
use App\Repositories\Gamification\AchievementRepository;
use Illuminate\Support\Facades\Event;

class AchievementsPipeline
{
    public function __construct(
        protected AchievementRepository $achievementRepository
    ) {
    }

    /**
     * Trigger XP gained event.
     */
    public function triggerXpGained(User $user, XpLog $log): array
    {
        $payload = [
            'xp_amount' => $log->xp_amount,
            'event_type' => $log->event_type->value ?? $log->event_type,
            'total_xp' => $user->xp_total,
        ];

        Event::dispatch(new UserEarnedXp($user, $log, $payload));

        // Log to achievement_logs
        $this->achievementRepository->logEvent(
            $user->id,
            'xp_gained',
            $payload
        );

        return [
            'success' => true,
            'type' => 'xp_gained',
            'payload' => $payload,
        ];
    }

    /**
     * Trigger level up event.
     */
    public function triggerLevelUp(User $user, Level $newLevel, ?Level $previousLevel = null): array
    {
        $payload = [
            'level_id' => $newLevel->id,
            'level_name' => $newLevel->name,
            'tier' => $newLevel->tier->value ?? $newLevel->tier,
            'xp_required' => $newLevel->xp_required,
            'previous_level_id' => $previousLevel?->id,
            'previous_level_name' => $previousLevel?->name,
        ];

        Event::dispatch(new UserLeveledUp($user, $newLevel, $previousLevel, $payload));

        // Log to achievement_logs
        $this->achievementRepository->logEvent(
            $user->id,
            'level_up',
            $payload
        );

        return [
            'success' => true,
            'type' => 'level_up',
            'payload' => $payload,
        ];
    }

    /**
     * Trigger badge unlocked event.
     */
    public function triggerBadgeUnlocked(User $user, Badge $badge): array
    {
        $payload = [
            'badge_id' => $badge->id,
            'badge_name' => $badge->name,
            'badge_slug' => $badge->slug,
            'badge_type' => $badge->type->value ?? $badge->type,
            'xp_reward' => $badge->xp_reward,
        ];

        Event::dispatch(new UserUnlockedBadge($user, $badge, $payload));

        // Log to achievement_logs
        $this->achievementRepository->logEvent(
            $user->id,
            'badge_unlocked',
            $payload
        );

        return [
            'success' => true,
            'type' => 'badge_unlocked',
            'payload' => $payload,
        ];
    }

    /**
     * Trigger task completed event.
     */
    public function triggerTaskCompleted(User $user, Task $task): array
    {
        $payload = [
            'task_id' => $task->id,
            'task_title' => $task->title,
            'frequency' => $task->frequency->value ?? $task->frequency,
            'xp_reward' => $task->xp_reward,
        ];

        Event::dispatch(new UserCompletedTask($user, $task, $payload));

        // Log to achievement_logs
        $this->achievementRepository->logEvent(
            $user->id,
            'task_completed',
            $payload
        );

        return [
            'success' => true,
            'type' => 'task_completed',
            'payload' => $payload,
        ];
    }

    /**
     * Trigger answer verified event.
     */
    public function triggerAnswerVerified(User $user, Answer $answer): array
    {
        $payload = [
            'answer_id' => $answer->id,
            'question_id' => $answer->question_id,
            'score' => $answer->score,
        ];

        Event::dispatch(new UserAnsweredVerified($user, $answer, $payload));

        // Log to achievement_logs
        $this->achievementRepository->logEvent(
            $user->id,
            'answer_verified',
            $payload
        );

        return [
            'success' => true,
            'type' => 'answer_verified',
            'payload' => $payload,
        ];
    }
}

