<?php

declare(strict_types=1);

namespace App\Services\User;

use App\Models\User;
use App\Models\ProfileVisit;
use App\Repositories\User\UserRepository;
use App\Services\Gamification\TaskService;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;

class UserService
{
    public function __construct(
        protected UserRepository $userRepository,
        protected ?TaskService $taskService = null
    ) {
    }

    /**
     * Get user profile.
     */
    public function getUserProfile(?string $username): ?User
    {
        if (!$username) {
            return null;
        }

        $user = $this->userRepository->findByUsername($username);

        if ($user) {
            $user->load(['level', 'badges', 'tasks', 'socialLinks.socialLinkType']);
        }

        return $user;
    }

    /**
     * Update user profile.
     */
    public function updateProfile(User $user, array $data, $request = null): User
    {
        // Handle avatar file upload if present
        if ($request && $request->hasFile('avatar')) {
            // Clear existing profile photo
            $user->clearMediaCollection('profile_photo');
            
            // Add new profile photo
            $user->addMediaFromRequest('avatar')
                 ->toMediaCollection('profile_photo');
        } elseif (isset($data['avatar']) && $data['avatar'] instanceof \Illuminate\Http\UploadedFile) {
            // Handle if avatar is passed directly in data
            $user->clearMediaCollection('profile_photo');
            $user->addMedia($data['avatar'])
                 ->toMediaCollection('profile_photo');
        }
        
        // Remove avatar from data array as it's handled by media library
        unset($data['avatar']);
        
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
            'social_links' => $user->socialLinks,
        ];
    }

    /**
     * Track profile visit (only counts if visiting someone else's profile).
     */
    public function trackProfileVisit(int $visitorId, int $visitedUserId): void
    {
        // Don't track if visiting own profile
        if ($visitorId === $visitedUserId) {
            return;
        }

        $today = Carbon::today();

        // Check if already visited today (prevent duplicates)
        $existingVisit = ProfileVisit::where('visitor_id', $visitorId)
            ->where('visited_user_id', $visitedUserId)
            ->whereDate('visited_at', $today)
            ->first();

        if (!$existingVisit) {
            ProfileVisit::create([
                'visitor_id' => $visitorId,
                'visited_user_id' => $visitedUserId,
                'visited_at' => $today->toDateString(),
            ]);

            // Auto-complete "Visit Someone's Profile" task if task service is available
            if ($this->taskService) {
                try {
                    $visitor = User::find($visitorId);
                    if ($visitor) {
                        $this->taskService->completeByTitle("Visit Someone's Profile", $visitor);
                    }
                } catch (\Exception $e) {
                    // Silently fail - task might not exist or already completed
                    logger()->debug('[UserService] Task auto-completion failed after profile visit', [
                        'visitor_id' => $visitorId,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }
    }
}

