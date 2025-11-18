<?php

declare(strict_types=1);

namespace App\Listeners\Gamification;

use App\Events\Gamification\UserEarnedXp;
use App\Events\Gamification\UserLeveledUp;
use App\Events\Gamification\UserUnlockedBadge;
use App\Events\Gamification\UserCompletedTask;
use App\Events\Gamification\UserAnsweredVerified;
use App\Repositories\Gamification\AchievementRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogAchievementEvent implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        protected AchievementRepository $achievementRepository
    ) {
    }

    /**
     * Handle XP earned event.
     */
    public function handleXpEarned(UserEarnedXp $event): void
    {
        $this->achievementRepository->logEvent(
            $event->user->id,
            'xp_gained',
            [
                'xp_amount' => $event->xpLog->xp_amount,
                'event_type' => $event->xpLog->event_type->value ?? $event->xpLog->event_type,
                'xp_log_id' => $event->xpLog->id,
                'total_xp' => $event->user->xp_total,
            ]
        );
    }

    /**
     * Handle level up event.
     */
    public function handleLevelUp(UserLeveledUp $event): void
    {
        $this->achievementRepository->logEvent(
            $event->user->id,
            'level_up',
            [
                'level_id' => $event->newLevel->id,
                'level_name' => $event->newLevel->name,
                'tier' => $event->newLevel->tier->value ?? $event->newLevel->tier,
                'xp_required' => $event->newLevel->xp_required,
                'previous_level_id' => $event->previousLevel?->id,
            ]
        );
    }

    /**
     * Handle badge unlocked event.
     */
    public function handleBadgeUnlocked(UserUnlockedBadge $event): void
    {
        $this->achievementRepository->logEvent(
            $event->user->id,
            'badge_unlocked',
            [
                'badge_id' => $event->badge->id,
                'badge_name' => $event->badge->name,
                'badge_slug' => $event->badge->slug,
                'badge_type' => $event->badge->type->value ?? $event->badge->type,
                'xp_reward' => $event->badge->xp_reward,
            ]
        );
    }

    /**
     * Handle task completed event.
     */
    public function handleTaskCompleted(UserCompletedTask $event): void
    {
        $this->achievementRepository->logEvent(
            $event->user->id,
            'task_completed',
            [
                'task_id' => $event->task->id,
                'task_title' => $event->task->title,
                'frequency' => $event->task->frequency->value ?? $event->task->frequency,
                'xp_reward' => $event->task->xp_reward,
            ]
        );
    }

    /**
     * Handle answer verified event.
     */
    public function handleAnswerVerified(UserAnsweredVerified $event): void
    {
        $this->achievementRepository->logEvent(
            $event->user->id,
            'answer_verified',
            [
                'answer_id' => $event->answer->id,
                'question_id' => $event->answer->question_id,
                'score' => $event->answer->score,
            ]
        );
    }
}

