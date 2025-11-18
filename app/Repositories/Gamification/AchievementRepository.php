<?php

declare(strict_types=1);

namespace App\Repositories\Gamification;

use App\Models\AchievementLog;
use Illuminate\Support\Collection;

class AchievementRepository
{
    public function __construct(protected AchievementLog $model)
    {
    }

    /**
     * Log an achievement event.
     */
    public function logEvent(int $userId, string $type, array $metadata = []): AchievementLog
    {
        return $this->model->create([
            'user_id' => $userId,
            'type' => $type,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Get achievement events for a user.
     */
    public function getUserEvents(int $userId, ?string $type = null, int $limit = 50): Collection
    {
        $query = $this->model->where('user_id', $userId);

        if ($type) {
            $query->where('type', $type);
        }

        return $query->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get recent achievement events.
     */
    public function getRecentEvents(int $limit = 100): Collection
    {
        return $this->model->with('user')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}

