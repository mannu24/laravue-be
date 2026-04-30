<?php

declare(strict_types=1);

namespace App\Listeners\Gamification;

use App\Events\Gamification\UserEarnedXp;
use App\Events\Gamification\UserLeveledUp;
use App\Events\Gamification\UserUnlockedBadge;
use App\Events\Gamification\UserCompletedTask;
use App\Events\Gamification\UserAnsweredVerified;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class DispatchRealTimeNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle XP earned event.
     */
    public function handleXpEarned(UserEarnedXp $event): void
    {
        Log::info('XP earned event', [
            'user_id' => $event->user->id,
            'xp_amount' => $event->xpLog->xp_amount,
            'event_type' => $event->xpLog->event_type,
        ]);
    }

    /**
     * Handle level up event.
     */
    public function handleLevelUp(UserLeveledUp $event): void
    {
        Log::info('Level up event', [
            'user_id' => $event->user->id,
            'new_level' => $event->newLevel->name,
            'tier' => $event->newLevel->tier,
        ]);
    }

    /**
     * Handle badge unlocked event.
     */
    public function handleBadgeUnlocked(UserUnlockedBadge $event): void
    {
        Log::info('Badge unlocked event', [
            'user_id' => $event->user->id,
            'badge_id' => $event->badge->id,
            'badge_name' => $event->badge->name,
        ]);
    }

    /**
     * Handle task completed event.
     */
    public function handleTaskCompleted(UserCompletedTask $event): void
    {
        Log::info('Task completed event', [
            'user_id' => $event->user->id,
            'task_id' => $event->task->id,
            'task_title' => $event->task->title,
        ]);
    }

    /**
     * Handle answer verified event.
     */
    public function handleAnswerVerified(UserAnsweredVerified $event): void
    {
        Log::info('Answer verified event', [
            'user_id' => $event->user->id,
            'answer_id' => $event->answer->id,
        ]);
    }
}
