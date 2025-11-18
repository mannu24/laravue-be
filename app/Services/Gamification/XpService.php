<?php

declare(strict_types=1);

namespace App\Services\Gamification;

use App\Enums\XpEventType;
use App\Models\User;
use App\Models\XpLog;
use App\Repositories\Gamification\XpRepository;
use App\Repositories\Gamification\LevelRepository;
use App\Repositories\User\UserRepository;
use App\Services\Achievements\AchievementsPipeline;
use Illuminate\Pagination\LengthAwarePaginator;

class XpService
{
    /**
     * XP amounts for different event types.
     */
    private const XP_AMOUNTS = [
        XpEventType::QUESTION_CREATED->value => 10,
        XpEventType::ANSWER_CREATED->value => 15,
        XpEventType::ANSWER_VERIFIED->value => 25,
        XpEventType::DAILY_TASK_COMPLETED->value => 20,
        XpEventType::WEEKLY_TASK_COMPLETED->value => 50,
        XpEventType::PROFILE_COMPLETED->value => 15,
        XpEventType::STREAK_MILESTONE->value => 30,
    ];

    public function __construct(
        protected XpRepository $xpRepository,
        protected LevelRepository $levelRepository,
        protected UserRepository $userRepository,
        protected ?AchievementsPipeline $achievements = null
    ) {
    }

    /**
     * Award XP for an event.
     */
    public function awardXpForEvent(User $user, XpEventType|string $eventType): XpLog
    {
        $eventTypeValue = $eventType instanceof XpEventType ? $eventType->value : $eventType;
        $xpAmount = self::XP_AMOUNTS[$eventTypeValue] ?? 0;

        if ($xpAmount <= 0) {
            throw new \InvalidArgumentException("Invalid event type: {$eventTypeValue}");
        }

        // Log XP
        $xpLog = $this->xpRepository->logXp(
            userId: $user->id,
            eventType: $eventTypeValue,
            amount: $xpAmount,
            metadata: ['event_type' => $eventTypeValue]
        );

        // Update user's total XP
        $this->userRepository->incrementXp($user, $xpAmount);

        // Recalculate level (this may trigger level up event)
        $updatedUser = $this->recalculateUserLevel($user->fresh());

        // Trigger achievement event
        if ($this->achievements) {
            $this->achievements->triggerXpGained($updatedUser, $xpLog);
        }

        return $xpLog;
    }

    /**
     * Recalculate user level based on total XP.
     */
    public function recalculateUserLevel(User $user): User
    {
        $previousLevel = $user->level;
        $currentLevel = $this->levelRepository->getCurrentLevelForXp($user->xp_total);

        if ($currentLevel && $user->level_id !== $currentLevel->id) {
            $user->update(['level_id' => $currentLevel->id]);
            
            // Trigger level up event
            if ($this->achievements) {
                $this->achievements->triggerLevelUp($user->fresh(), $currentLevel, $previousLevel);
            }
        }

        return $user->fresh();
    }

    /**
     * Get XP history for a user.
     */
    public function getXpHistory(int $userId, int $perPage = 20): LengthAwarePaginator
    {
        return $this->xpRepository->getUserLogs($userId, $perPage);
    }

    /**
     * Get user XP summary.
     */
    public function getUserXpSummary(User $user): array
    {
        $totalXp = $this->xpRepository->getUserTotalXp($user->id);
        $currentLevel = $this->levelRepository->getCurrentLevelForXp($user->xp_total);
        $nextLevel = $currentLevel 
            ? $this->levelRepository->getNextLevel($currentLevel->id)
            : null;

        return [
            'total_xp' => $totalXp,
            'current_level' => $currentLevel,
            'next_level' => $nextLevel,
            'xp_to_next_level' => $nextLevel 
                ? max(0, $nextLevel->xp_required - $totalXp)
                : null,
        ];
    }
}

