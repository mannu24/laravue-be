<?php

declare(strict_types=1);

namespace App\Repositories\Gamification;

use App\Models\XpLog;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class XpRepository
{
    public function __construct(protected XpLog $model)
    {
    }

    /**
     * Log XP for a user.
     */
    public function logXp(int $userId, string $eventType, int $amount, array $metadata = []): XpLog
    {
        return $this->model->create([
            'user_id' => $userId,
            'event_type' => $eventType,
            'xp_amount' => $amount,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Get total XP for a user.
     */
    public function getUserTotalXp(int $userId): int
    {
        return (int) $this->model->where('user_id', $userId)
            ->sum('xp_amount');
    }

    /**
     * Get XP logs for a user.
     */
    public function getUserLogs(int $userId, int $perPage = 20): LengthAwarePaginator
    {
        return $this->model->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
}

