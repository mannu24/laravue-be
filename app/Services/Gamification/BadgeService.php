<?php

declare(strict_types=1);

namespace App\Services\Gamification;

use App\Models\User;
use App\Models\Badge;
use App\Repositories\Gamification\BadgeRepository;
use App\Services\Achievements\AchievementsPipeline;
use Illuminate\Support\Collection;

class BadgeService
{
    public function __construct(
        protected BadgeRepository $badgeRepository,
        protected XpService $xpService,
        protected ?AchievementsPipeline $achievements = null
    ) {
    }

    /**
     * Check and award badge to user.
     */
    public function checkAndAwardBadge(User $user, string $badgeSlug): ?Badge
    {
        $badge = $this->badgeRepository->findBySlug($badgeSlug);

        if (!$badge) {
            return null;
        }

        // Check if user already has this badge
        if ($user->badges()->where('badge_id', $badge->id)->exists()) {
            return $badge;
        }

        // Award badge
        $this->badgeRepository->awardBadge($user->id, $badge->id);

        // Award XP for badge if it has xp_reward
        if ($badge->xp_reward > 0) {
            $this->xpService->awardCustomXp(
                $user,
                'badge_unlocked',
                $badge->xp_reward,
                [
                    'badge_id' => $badge->id,
                    'badge_slug' => $badge->slug,
                    'badge_name' => $badge->name,
                ]
            );
        }

        // Trigger achievement event
        if ($this->achievements) {
            $this->achievements->triggerBadgeUnlocked($user, $badge);
        }

        return $badge;
    }

    /**
     * Get all badges.
     */
    public function getAllBadges(): Collection
    {
        return $this->badgeRepository->getAll();
    }

    /**
     * Get badges for a user.
     */
    public function getBadgesForUser(int $userId): Collection
    {
        return $this->badgeRepository->getBadgesForUser($userId);
    }

    /**
     * Award badge to user.
     */
    public function awardBadge(User $user, Badge $badge): void
    {
        // Check if user already has this badge
        if ($user->badges()->where('badge_id', $badge->id)->exists()) {
            return;
        }

        $this->badgeRepository->awardBadge($user->id, $badge->id);

        // Award XP for badge if it has xp_reward
        if ($badge->xp_reward > 0) {
            $this->xpService->awardCustomXp(
                $user,
                'badge_unlocked',
                $badge->xp_reward,
                [
                    'badge_id' => $badge->id,
                    'badge_slug' => $badge->slug,
                    'badge_name' => $badge->name,
                ]
            );
        }

        // Trigger achievement event
        if ($this->achievements) {
            $this->achievements->triggerBadgeUnlocked($user, $badge);
        }
    }
}

