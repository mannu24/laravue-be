<?php

declare(strict_types=1);

namespace App\Services\Gamification;

use App\Enums\XpEventType;
use App\Models\User;
use App\Models\XpLog;
use App\Models\Level;
use App\Models\Badge;
use Illuminate\Support\Collection;

/**
 * Unified Gamification Service
 * Handles XP, tasks, badges, and level ups in a single flow
 */
class GamificationService
{
    public function __construct(
        protected XpService $xpService,
        protected TaskService $taskService,
        protected BadgeService $badgeService
    ) {
    }

    /**
     * Award XP and handle all gamification effects (tasks, badges, level ups)
     * This is the main unified method that handles everything at once
     * 
     * @param User $user
     * @param XpEventType|string $eventType
     * @param array $metadata Additional metadata for the event
     * @return array Returns array with xp_log, level_up, badges_unlocked, tasks_completed
     */
    public function awardXpAndProcess(User $user, XpEventType|string $eventType, array $metadata = []): array
    {
        $result = [
            'xp_log' => null,
            'level_up' => null,
            'badges_unlocked' => [],
            'tasks_completed' => [],
        ];

        // Get previous level before XP award
        $previousLevel = $user->level;
        $previousXp = $user->xp_total;

        // Award XP (this automatically handles level ups)
        $xpLog = $this->xpService->awardXpForEvent($user, $eventType);
        $result['xp_log'] = $xpLog;

        // Refresh user to get updated XP and level
        $user = $user->fresh(['level']);

        // Check for level up
        if ($previousLevel && $user->level && $previousLevel->id !== $user->level->id) {
            $result['level_up'] = [
                'previous_level' => $previousLevel,
                'new_level' => $user->level,
            ];
        }

        // Auto-complete related tasks based on event type
        $completedTasks = $this->autoCompleteRelatedTasks($user, $eventType);
        $result['tasks_completed'] = $completedTasks;

        // Check for badges based on XP milestones and other criteria
        $unlockedBadges = $this->checkAndAwardBadges($user, $eventType, $previousXp, $user->xp_total);
        $result['badges_unlocked'] = $unlockedBadges;

        return $result;
    }

    /**
     * Auto-complete tasks related to the event type
     */
    protected function autoCompleteRelatedTasks(User $user, XpEventType|string $eventType): array
    {
        $completedTasks = [];
        $eventTypeValue = $eventType instanceof XpEventType ? $eventType->value : $eventType;

        // Map event types to task titles
        $taskMapping = [
            XpEventType::QUESTION_CREATED->value => 'Ask 1 Question',
            XpEventType::ANSWER_CREATED->value => 'Answer 1 Question',
        ];

        $taskTitle = $taskMapping[$eventTypeValue] ?? null;

        if ($taskTitle) {
            try {
                $userTask = $this->taskService->completeByTitle($taskTitle, $user);
                if ($userTask) {
                    $completedTasks[] = [
                        'task_id' => $userTask->task->id,
                        'task_title' => $userTask->task->title,
                        'xp_reward' => $userTask->task->xp_reward,
                    ];
                }
            } catch (\Exception $e) {
                // Task might not exist or already completed, silently continue
                logger()->debug('[GamificationService] Task auto-completion failed', [
                    'task_title' => $taskTitle,
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $completedTasks;
    }

    /**
     * Check and award badges based on XP milestones and other criteria
     */
    protected function checkAndAwardBadges(User $user, XpEventType|string $eventType, int $previousXp, int $currentXp): array
    {
        $unlockedBadges = [];

        // Check XP milestone badges (100, 500, 1000, 5000, etc.)
        $milestones = [100, 500, 1000, 2500, 5000, 10000, 25000, 50000];
        
        foreach ($milestones as $milestone) {
            // Check if user just crossed this milestone
            if ($previousXp < $milestone && $currentXp >= $milestone) {
                $badgeSlug = $this->getXpMilestoneBadgeSlug($milestone);
                if ($badgeSlug) {
                    try {
                        $badge = $this->badgeService->checkAndAwardBadge($user, $badgeSlug);
                        if ($badge) {
                            $unlockedBadges[] = [
                                'badge_id' => $badge->id,
                                'badge_name' => $badge->name,
                                'badge_slug' => $badge->slug,
                                'badge_type' => $badge->type->value ?? $badge->type,
                                'xp_reward' => $badge->xp_reward,
                            ];
                        }
                    } catch (\Exception $e) {
                        logger()->debug('[GamificationService] Badge check failed', [
                            'badge_slug' => $badgeSlug,
                            'user_id' => $user->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }
        }

        // Check level-based badges
        if ($user->level) {
            $levelBadgeSlug = $this->getLevelBadgeSlug($user->level);
            if ($levelBadgeSlug) {
                try {
                    $badge = $this->badgeService->checkAndAwardBadge($user, $levelBadgeSlug);
                    if ($badge) {
                        $unlockedBadges[] = [
                            'badge_id' => $badge->id,
                            'badge_name' => $badge->name,
                            'badge_slug' => $badge->slug,
                            'badge_type' => $badge->type->value ?? $badge->type,
                            'xp_reward' => $badge->xp_reward,
                        ];
                    }
                } catch (\Exception $e) {
                    // Badge might not exist, silently continue
                }
            }
        }

        return $unlockedBadges;
    }

    /**
     * Get badge slug for XP milestone
     */
    protected function getXpMilestoneBadgeSlug(int $milestone): ?string
    {
        $mapping = [
            100 => 'first-100-xp',
            500 => '500-xp-milestone',
            1000 => '1000-xp-milestone',
            2500 => '2500-xp-milestone',
            5000 => '5000-xp-milestone',
            10000 => '10000-xp-milestone',
            25000 => '25000-xp-milestone',
            50000 => '50000-xp-milestone',
        ];

        return $mapping[$milestone] ?? null;
    }

    /**
     * Get badge slug for level
     */
    protected function getLevelBadgeSlug(Level $level): ?string
    {
        // Map level tiers to badge slugs
        $tier = $level->tier->value ?? $level->tier;
        
        $mapping = [
            'beginner' => 'beginner-level',
            'intermediate' => 'intermediate-level',
            'advanced' => 'advanced-level',
            'expert' => 'expert-level',
            'master' => 'master-level',
        ];

        return $mapping[$tier] ?? null;
    }
}

