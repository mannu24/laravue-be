<?php

declare(strict_types=1);

namespace App\Repositories\Gamification;

use App\Models\Level;
use Illuminate\Database\Eloquent\Collection;

class LevelRepository
{
    public function __construct(protected Level $model)
    {
    }

    /**
     * Get all levels ordered by XP required.
     */
    public function getAllLevels(): Collection
    {
        return $this->model->orderBy('xp_required', 'asc')->get();
    }

    /**
     * Get current level for given XP amount.
     */
    public function getCurrentLevelForXp(int $xp): ?Level
    {
        return $this->model->where('xp_required', '<=', $xp)
            ->orderBy('xp_required', 'desc')
            ->first();
    }

    /**
     * Get next level after current level.
     */
    public function getNextLevel(int $currentLevelId): ?Level
    {
        $currentLevel = $this->model->find($currentLevelId);
        
        if (!$currentLevel) {
            return null;
        }

        return $this->model->where('xp_required', '>', $currentLevel->xp_required)
            ->orderBy('xp_required', 'asc')
            ->first();
    }
}

