<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\XpEventType;
use App\Models\User;
use App\Services\Gamification\XpService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckUserStreaks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gamification:check-streaks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and update user streaks based on last_active_at timestamp';

    /**
     * Execute the console command.
     */
    public function handle(XpService $xpService): int
    {
        $this->info('Starting streak check...');

        $startTime = now();
        $streaksUpdated = 0;
        $streaksReset = 0;
        $milestonesReached = 0;

        try {
            // Get all users with last_active_at set
            $users = User::whereNotNull('last_active_at')
                ->get();

            $this->info("Processing {$users->count()} users...");

            $streakMilestones = [3, 7, 14, 30, 60, 100, 365];

            DB::transaction(function () use ($users, $xpService, $streakMilestones, &$streaksUpdated, &$streaksReset, &$milestonesReached) {
                foreach ($users as $user) {
                    $lastActive = $user->last_active_at;
                    $currentStreak = $user->streak_days ?? 0;
                    $shouldUpdate = false;
                    $newStreak = $currentStreak;

                    // Check if user was active today or yesterday
                    $wasActiveToday = $lastActive && $lastActive->isToday();
                    $wasActiveYesterday = $lastActive && $lastActive->isYesterday();
                    $lastActiveDaysAgo = $lastActive ? $lastActive->diffInDays(now(), false) : null;

                    if ($wasActiveToday) {
                        // User was active today - maintain or increment streak
                        if ($wasActiveYesterday || $currentStreak === 0) {
                            // Continue streak (was active yesterday) or start new streak
                            $newStreak = $currentStreak + 1;
                            $shouldUpdate = true;
                        } elseif ($currentStreak > 0) {
                            // Already counted today, no change needed
                            continue;
                        }
                    } elseif ($wasActiveYesterday && $currentStreak === 0) {
                        // User was active yesterday but has no streak - start streak
                        $newStreak = 1;
                        $shouldUpdate = true;
                    } elseif ($lastActiveDaysAgo !== null && $lastActiveDaysAgo > 1) {
                        // User hasn't been active for more than 1 day - reset streak
                        if ($currentStreak > 0) {
                            $newStreak = 0;
                            $shouldUpdate = true;
                            $streaksReset++;
                        }
                    }

                    if ($shouldUpdate && $newStreak !== $currentStreak) {
                        // Check for streak milestones
                        $oldStreak = $currentStreak;
                        $reachedMilestone = false;

                        foreach ($streakMilestones as $milestone) {
                            if ($oldStreak < $milestone && $newStreak >= $milestone) {
                                // User reached a milestone
                                try {
                                    $xpService->awardXpForEvent($user, XpEventType::STREAK_MILESTONE);
                                    $reachedMilestone = true;
                                    $milestonesReached++;
                                    $this->line("User {$user->id} reached {$milestone}-day streak milestone!");
                                } catch (\Exception $e) {
                                    Log::warning("Failed to award XP for streak milestone", [
                                        'user_id' => $user->id,
                                        'milestone' => $milestone,
                                        'error' => $e->getMessage(),
                                    ]);
                                }
                                break;
                            }
                        }

                        // Update user streak
                        $user->update([
                            'streak_days' => $newStreak,
                        ]);

                        $streaksUpdated++;

                        if ($reachedMilestone) {
                            Log::info('User reached streak milestone', [
                                'user_id' => $user->id,
                                'old_streak' => $oldStreak,
                                'new_streak' => $newStreak,
                            ]);
                        }
                    }
                }
            });

            $duration = now()->diffInSeconds($startTime);

            $this->info("Streak check completed!");
            $this->info("Updated {$streaksUpdated} user streaks");
            $this->info("Reset {$streaksReset} streaks");
            $this->info("Reached {$milestonesReached} streak milestones");
            $this->info("Duration: {$duration} seconds");

            Log::info('Streak check completed', [
                'streaks_updated' => $streaksUpdated,
                'streaks_reset' => $streaksReset,
                'milestones_reached' => $milestonesReached,
                'duration_seconds' => $duration,
            ]);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Error checking streaks: {$e->getMessage()}");
            Log::error('Streak check failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return Command::FAILURE;
        }
    }
}

