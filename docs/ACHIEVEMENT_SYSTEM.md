# Achievement Event Dispatch System Documentation

## Overview

The Achievement Event Dispatch System automatically triggers events for all gamification milestones (XP gains, level-ups, badge unlocks, task completions, and answer verifications). These events are logged, can be broadcast in real-time, and trigger frontend animations.

## Architecture

### Events (`app/Events/Gamification/`)

All events extend Laravel's base event classes and contain:
- `User` model
- Related model (XpLog, Level, Badge, Task, Answer)
- Payload array with additional data

**Events:**
- `UserEarnedXp` - Fired when user gains XP
- `UserLeveledUp` - Fired when user reaches a new level
- `UserUnlockedBadge` - Fired when user earns a badge
- `UserCompletedTask` - Fired when user completes a task
- `UserAnsweredVerified` - Fired when user's answer is verified

### Listeners (`app/Listeners/Gamification/`)

Three listeners handle each event:

1. **DispatchRealTimeNotification**
   - Placeholder for Pusher/Echo broadcasting
   - Logs events for debugging
   - Queued for async processing

2. **LogAchievementEvent**
   - Writes to `achievement_logs` table
   - Stores event metadata for history
   - Queued for async processing

3. **QueueAchievementEffects**
   - Placeholder for frontend animation triggers
   - Can dispatch jobs for animation sequences
   - Queued for async processing

### AchievementsPipeline Service (`app/Services/Achievements/`)

Central service that:
- Dispatches events via Laravel's Event system
- Logs to `achievement_logs` table
- Returns response arrays for controllers

**Methods:**
- `triggerXpGained(User $user, XpLog $log)`
- `triggerLevelUp(User $user, Level $newLevel, ?Level $previousLevel)`
- `triggerBadgeUnlocked(User $user, Badge $badge)`
- `triggerTaskCompleted(User $user, Task $task)`
- `triggerAnswerVerified(User $user, Answer $answer)`

### Achievement Logs

**Migration:** `create_achievement_logs_table`
- `id` - Primary key
- `user_id` - Foreign key to users
- `type` - Event type (xp_gained, level_up, badge_unlocked, etc.)
- `metadata` - JSON field with event details
- `created_at`, `updated_at` - Timestamps

**Model:** `App\Models\AchievementLog`
- Relationships to User
- JSON casting for metadata

**Repository:** `App\Repositories\Gamification\AchievementRepository`
- `logEvent($userId, $type, $metadata)` - Log an event
- `getUserEvents($userId, $type, $limit)` - Get user's events
- `getRecentEvents($limit)` - Get recent events

## Service Integration

All gamification services now dispatch events:

### XpService
- Dispatches `UserEarnedXp` when XP is awarded
- Dispatches `UserLeveledUp` when level changes (via `recalculateUserLevel`)

### BadgeService
- Dispatches `UserUnlockedBadge` when badge is awarded

### TaskService
- Dispatches `UserCompletedTask` when task is completed

### AnswerService
- Dispatches `UserAnsweredVerified` when answer is verified

## Event Registration

Events are registered in `app/Providers/EventServiceProvider.php` and the provider is registered in `bootstrap/providers.php`.

## Usage Example

```php
// In a controller or service
$xpService->awardXpForEvent($user, XpEventType::QUESTION_CREATED);
// This automatically:
// 1. Awards XP
// 2. Updates user level (if needed)
// 3. Dispatches UserEarnedXp event
// 4. Dispatches UserLeveledUp event (if level changed)
// 5. Logs to achievement_logs
// 6. Queues real-time notifications
// 7. Queues animation effects
```

## Frontend Integration

The frontend can:
1. Listen to real-time events via Pusher/Echo (when implemented)
2. Poll achievement_logs API endpoint (to be created)
3. Receive event data in API responses

## Database Seeding

Run seeders to populate initial data:

```bash
php artisan db:seed
# Or seed individually:
php artisan db:seed --class=LevelSeeder
php artisan db:seed --class=BadgeSeeder
php artisan db:seed --class=TaskSeeder
php artisan db:seed --class=UserSeeder
```

## Testing

Test the achievement system:

```php
// In tinker or a test
$user = User::first();
$xpService = app(\App\Services\Gamification\XpService::class);
$xpService->awardXpForEvent($user, \App\Enums\XpEventType::QUESTION_CREATED);

// Check logs
\App\Models\AchievementLog::latest()->first();
```

## Next Steps

1. Implement Pusher/Echo broadcasting in `DispatchRealTimeNotification`
2. Create API endpoint to fetch achievement logs
3. Implement animation job dispatchers in `QueueAchievementEffects`
4. Add achievement log API resources
5. Create frontend event listeners for real-time updates

