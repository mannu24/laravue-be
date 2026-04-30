<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\HttpResponse;
use App\Models\AchievementLog;
use App\Models\Badge;
use App\Models\Level;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AchievementController extends Controller
{
    use HttpResponse;

    /**
     * Get recent achievement events for the authenticated user.
     *
     * Used by the frontend polling composable to detect new
     * badges, level ups, and auto-completed tasks since the last check.
     */
    public function recentAchievements(Request $request): JsonResponse
    {
        $user = $request->user();
        $since = $request->query('since');

        $query = AchievementLog::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(20);

        if ($since) {
            $query->where('created_at', '>', $since);
        }

        $logs = $query->get();

        $badges = [];
        $levelUp = null;
        $tasks = [];

        foreach ($logs as $log) {
            $meta = $log->metadata ?? [];

            switch ($log->type) {
                case 'badge_unlocked':
                    // Fetch full badge from DB for complete data (icon_path, description, etc.)
                    $badgeId = $meta['badge_id'] ?? null;
                    $badge = $badgeId ? Badge::find($badgeId) : null;

                    $badges[] = [
                        'id' => $badgeId,
                        'name' => $badge->name ?? $meta['badge_name'] ?? '',
                        'slug' => $badge->slug ?? $meta['badge_slug'] ?? '',
                        'description' => $badge->description ?? '',
                        'type' => $badge ? ($badge->type->value ?? $badge->type) : ($meta['badge_type'] ?? ''),
                        'icon_path' => $badge->icon_path ?? null,
                        'xp_reward' => $badge->xp_reward ?? $meta['xp_reward'] ?? 0,
                    ];
                    break;

                case 'level_up':
                    // Only return the most recent level up
                    if (!$levelUp) {
                        $levelId = $meta['level_id'] ?? null;
                        $level = $levelId ? Level::find($levelId) : null;
                        $previousLevelId = $meta['previous_level_id'] ?? null;
                        $previousLevel = $previousLevelId ? Level::find($previousLevelId) : null;

                        $levelUp = [
                            'level' => [
                                'id' => $levelId,
                                'name' => $level->name ?? $meta['level_name'] ?? '',
                                'xp_required' => $level->xp_required ?? $meta['xp_required'] ?? 0,
                                'tier' => $level ? ($level->tier->value ?? $level->tier) : ($meta['tier'] ?? ''),
                            ],
                            'previous_level' => $previousLevel ? [
                                'id' => $previousLevel->id,
                                'name' => $previousLevel->name,
                                'xp_required' => $previousLevel->xp_required,
                                'tier' => $previousLevel->tier->value ?? $previousLevel->tier,
                            ] : null,
                        ];
                    }
                    break;

                case 'task_completed':
                    $tasks[] = [
                        'id' => $meta['task_id'] ?? null,
                        'title' => $meta['task_title'] ?? '',
                        'frequency' => $meta['frequency'] ?? '',
                        'xp_reward' => $meta['xp_reward'] ?? 0,
                        'source' => $meta['source'] ?? 'auto',
                    ];
                    break;
            }
        }

        return $this->success([
            'badges' => $badges,
            'level_up' => $levelUp,
            'tasks' => $tasks,
        ]);
    }
}
