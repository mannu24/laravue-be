<?php

declare(strict_types=1);

namespace App\Repositories\Gamification;

use App\Models\Badge;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class BadgeRepository
{
    public function __construct(protected Badge $model)
    {
    }

    /**
     * Get all badges.
     */
    public function getAll(): Collection
    {
        return $this->model->where('is_active', true)
            ->orderBy('name', 'asc')
            ->get();
    }

    /**
     * Find badge by slug.
     */
    public function findBySlug(string $slug): ?Badge
    {
        return $this->model->where('slug', $slug)->first();
    }

    /**
     * Award badge to user.
     */
    public function awardBadge(int $userId, int $badgeId): void
    {
        $user = User::findOrFail($userId);
        $badge = $this->model->findOrFail($badgeId);

        // Check if user already has this badge
        if (!$user->badges()->where('badge_id', $badgeId)->exists()) {
            $user->badges()->attach($badgeId, [
                'awarded_at' => now(),
            ]);
        }
    }

    /**
     * Get badges for a user.
     */
    public function getBadgesForUser(int $userId): Collection
    {
        $user = User::findOrFail($userId);
        return $user->badges()
            ->orderBy('user_badges.awarded_at', 'desc')
            ->get();
    }
}

