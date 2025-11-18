<?php

declare(strict_types=1);

namespace App\Providers;

use App\Events\Gamification\UserAnsweredVerified;
use App\Events\Gamification\UserCompletedTask;
use App\Events\Gamification\UserEarnedXp;
use App\Events\Gamification\UserLeveledUp;
use App\Events\Gamification\UserUnlockedBadge;
use App\Listeners\Gamification\DispatchRealTimeNotification;
use App\Listeners\Gamification\LogAchievementEvent;
use App\Listeners\Gamification\QueueAchievementEffects;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        UserEarnedXp::class => [
            [DispatchRealTimeNotification::class, 'handleXpEarned'],
            [LogAchievementEvent::class, 'handleXpEarned'],
            [QueueAchievementEffects::class, 'handleXpEarned'],
        ],
        UserLeveledUp::class => [
            [DispatchRealTimeNotification::class, 'handleLevelUp'],
            [LogAchievementEvent::class, 'handleLevelUp'],
            [QueueAchievementEffects::class, 'handleLevelUp'],
        ],
        UserUnlockedBadge::class => [
            [DispatchRealTimeNotification::class, 'handleBadgeUnlocked'],
            [LogAchievementEvent::class, 'handleBadgeUnlocked'],
            [QueueAchievementEffects::class, 'handleBadgeUnlocked'],
        ],
        UserCompletedTask::class => [
            [DispatchRealTimeNotification::class, 'handleTaskCompleted'],
            [LogAchievementEvent::class, 'handleTaskCompleted'],
            [QueueAchievementEffects::class, 'handleTaskCompleted'],
        ],
        UserAnsweredVerified::class => [
            [DispatchRealTimeNotification::class, 'handleAnswerVerified'],
            [LogAchievementEvent::class, 'handleAnswerVerified'],
            [QueueAchievementEffects::class, 'handleAnswerVerified'],
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}

