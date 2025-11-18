<?php

declare(strict_types=1);

namespace App\Services\Gamification;

use App\Models\Level;
use App\Models\User;
use App\Repositories\Gamification\LevelRepository;
use App\Services\Achievements\AchievementsPipeline;

class LevelService
{
    public function __construct(
        protected LevelRepository $levelRepository,
        protected ?AchievementsPipeline $achievements = null
    ) {
    }

    /**
     * Get current level for given XP amount.
     */
    public function getCurrentLevel(int $xp): ?Level
    {
        return $this->levelRepository->getCurrentLevelForXp($xp);
    }

    /**
     * Get next level after current level.
     */
    public function getNextLevel(int $currentLevelId): ?Level
    {
        return $this->levelRepository->getNextLevel($currentLevelId);
    }

    /**
     * Get level progress for user.
     */
    public function getLevelProgress(User $user): array
    {
        $currentLevel = $this->levelRepository->getCurrentLevelForXp($user->xp_total ?? 0);
        $nextLevel = $currentLevel 
            ? $this->levelRepository->getNextLevel($currentLevel->id)
            : null;

        $xpCurrent = $user->xp_total ?? 0;
        $xpRequired = $nextLevel ? $nextLevel->xp_required : ($currentLevel ? $currentLevel->xp_required : 0);
        $xpForCurrentLevel = $currentLevel ? $currentLevel->xp_required : 0;
        
        // Calculate progress: XP earned in current level / XP needed for next level
        $xpInCurrentLevel = $xpCurrent - $xpForCurrentLevel;
        $xpNeededForNext = $nextLevel ? ($nextLevel->xp_required - $xpForCurrentLevel) : 0;
        
        $progressPercent = $xpNeededForNext > 0 
            ? min(100, round(($xpInCurrentLevel / $xpNeededForNext) * 100, 2))
            : 100;

        return [
            'xp_current' => $xpCurrent,
            'xp_required' => $xpRequired,
            'xp_for_current_level' => $xpForCurrentLevel,
            'xp_in_current_level' => max(0, $xpInCurrentLevel),
            'xp_needed_for_next' => max(0, $xpNeededForNext),
            'progress_percent' => $progressPercent,
            'current_level' => $currentLevel,
            'next_level' => $nextLevel,
        ];
    }
}

