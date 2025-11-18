<?php

declare(strict_types=1);

namespace App\Services\User;

use App\Models\User;
use App\Repositories\User\UserRepository;
use Illuminate\Support\Collection;

class UserService
{
    public function __construct(
        protected UserRepository $userRepository
    ) {
    }

    /**
     * Get user profile.
     */
    public function getUserProfile(?string $username): ?User
    {
        return $this->userRepository->findByUsername($username);
    }

    /**
     * Update user profile.
     */
    public function updateProfile(User $user, array $data): User
    {
        return $this->userRepository->updateProfile($user, $data);
    }

    /**
     * Increment user XP.
     */
    public function incrementXp(User $user, int $value): User
    {
        return $this->userRepository->incrementXp($user, $value);
    }

    /**
     * Get user with gamification data.
     */
    public function getUserWithGamification(int $userId): array
    {
        $user = $this->userRepository->findWithRelations($userId);
        
        if (!$user) {
            throw new \Exception("User not found");
        }

        return [
            'user' => $user,
            'xp_total' => $user->xp_total ?? 0,
            'level' => $user->level,
            'streak_days' => $user->streak_days ?? 0,
            'badges' => $user->badges,
            'tasks' => $user->tasks,
            'recent_xp_logs' => $user->xpLogs,
        ];
    }
}

