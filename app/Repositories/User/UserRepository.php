<?php

declare(strict_types=1);

namespace App\Repositories\User;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class UserRepository
{
    public function __construct(protected User $model)
    {
    }

    /**
     * Find user by ID.
     */
    public function findById(int $id): ?User
    {
        return $this->model->find($id);
    }

    /**
     * Find user by username (case-insensitive).
     */
    public function findByUsername(string $username): ?User
    {
        return $this->model->whereRaw('LOWER(username) = ?', [strtolower($username)])->first();
    }

    /**
     * Update user profile.
     */
    public function updateProfile(User $user, array $data): User
    {
        $user->update($data);
        return $user->fresh();
    }

    /**
     * Increment user XP.
     */
    public function incrementXp(User $user, int $amount): User
    {
        $user->increment('xp_total', $amount);
        return $user->fresh();
    }

    /**
     * Get streak information for user.
     */
    public function getStreakInfo(User $user): array
    {
        return [
            'streak_days' => $user->streak_days,
            'last_active_at' => $user->last_active_at,
        ];
    }

    /**
     * Find user with relations.
     */
    public function findWithRelations(int $id): ?User
    {
        return $this->model->with([
            'level',
            'badges',
            'xpLogs' => function ($query) {
                $query->latest()->limit(10);
            },
            'tasks' => function ($query) {
                $query->where('status', 'pending');
            },
            'socialLinks' => function ($query) {
                $query->with('socialLinkType')->orderBy('position');
            },
        ])->find($id);
    }
}

