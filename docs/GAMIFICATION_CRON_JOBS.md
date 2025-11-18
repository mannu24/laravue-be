# Gamification Cron Jobs Documentation

## Overview

This document describes the automated cron jobs that maintain the gamification system, including daily/weekly task resets and streak checking.

## Available Commands

### 1. Daily Task Reset
**Command:** `gamification:reset-daily-tasks`  
**Schedule:** Daily at 00:00 UTC  
**Purpose:** Resets incomplete daily tasks from the previous day and assigns new daily tasks to all active users.

**What it does:**
- Finds all incomplete daily tasks assigned before today
- Resets their assignment date
- Assigns new daily tasks to users who don't have them for today
- Logs the operation for monitoring

**Manual execution:**
```bash
php artisan gamification:reset-daily-tasks
```

### 2. Weekly Task Reset
**Command:** `gamification:reset-weekly-tasks`  
**Schedule:** Every Monday at 00:00 UTC  
**Purpose:** Resets incomplete weekly tasks from the previous week and assigns new weekly tasks to all active users.

**What it does:**
- Finds all incomplete weekly tasks assigned before this week
- Resets their assignment date
- Assigns new weekly tasks to users who don't have them for this week
- Logs the operation for monitoring

**Manual execution:**
```bash
php artisan gamification:reset-weekly-tasks
```

### 3. Streak Checker
**Command:** `gamification:check-streaks`  
**Schedule:** Daily at 01:00 UTC  
**Purpose:** Checks user activity and updates streak counters based on `last_active_at` timestamp.

**What it does:**
- Checks if users were active today or yesterday
- Increments streak for active users
- Resets streak for users inactive for more than 1 day
- Awards XP for streak milestones (3, 7, 14, 30, 60, 100, 365 days)
- Logs streak updates and milestones

**Streak Logic:**
- User active today → Maintain or increment streak
- User active yesterday → Continue streak
- User inactive > 1 day → Reset streak to 0
- Reaching milestone → Award XP (30 XP per milestone)

**Manual execution:**
```bash
php artisan gamification:check-streaks
```

### 4. Test Command
**Command:** `gamification:test-cron`  
**Purpose:** Test cron jobs manually for debugging

**Usage:**
```bash
# Test daily reset
php artisan gamification:test-cron daily

# Test weekly reset
php artisan gamification:test-cron weekly

# Test streak checker
php artisan gamification:test-cron streaks

# Test all jobs
php artisan gamification:test-cron all
```

## Scheduling

All cron jobs are registered in `routes/console.php` using Laravel's task scheduler.

### Schedule Configuration

```php
// Daily task reset - runs at midnight UTC
Schedule::command('gamification:reset-daily-tasks')
    ->dailyAt('00:00')
    ->timezone('UTC')
    ->withoutOverlapping()
    ->runInBackground();

// Weekly task reset - runs Monday at midnight UTC
Schedule::command('gamification:reset-weekly-tasks')
    ->weeklyOn(1, '00:00')
    ->timezone('UTC')
    ->withoutOverlapping()
    ->runInBackground();

// Streak checker - runs at 1 AM UTC
Schedule::command('gamification:check-streaks')
    ->dailyAt('01:00')
    ->timezone('UTC')
    ->withoutOverlapping()
    ->runInBackground();
```

## Setting Up Cron on Server

To ensure Laravel's scheduler runs, add this to your server's crontab:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

This runs every minute and Laravel will execute scheduled tasks at their designated times.

## Monitoring

All commands log their execution to Laravel's log file:

- **Success:** Logged with `Log::info()` including counts and duration
- **Errors:** Logged with `Log::error()` including full stack trace

Check logs at: `storage/logs/laravel.log`

### Log Examples

**Success:**
```
[2024-01-15 00:00:15] local.INFO: Daily task reset completed {"reset_count":150,"assigned_count":300,"duration_seconds":5}
```

**Error:**
```
[2024-01-15 00:00:20] local.ERROR: Daily task reset failed {"error":"Database connection timeout","trace":"..."}
```

## Performance Considerations

- All commands use database transactions for data integrity
- Commands run in background to avoid blocking
- `withoutOverlapping()` prevents concurrent executions
- Commands process users in batches for efficiency

## Troubleshooting

### Cron jobs not running
1. Check if `schedule:run` is in server crontab
2. Verify Laravel scheduler is working: `php artisan schedule:list`
3. Check application logs for errors

### Tasks not resetting
1. Verify tasks exist in database with `is_active = true`
2. Check task frequency enum values match
3. Review command output for errors

### Streaks not updating
1. Ensure users have `last_active_at` set
2. Verify `last_active_at` is updated when users log in
3. Check streak logic matches your timezone requirements

## Best Practices

1. **Timezone:** All cron jobs use UTC. Adjust if needed for your application.
2. **Testing:** Always test commands manually before deploying
3. **Monitoring:** Set up alerts for cron job failures
4. **Backup:** Consider backing up data before major resets
5. **Performance:** Monitor execution times and optimize if needed

## Related Files

- Commands: `app/Console/Commands/`
- Scheduling: `routes/console.php`
- Services: `app/Services/Gamification/`
- Models: `app/Models/User.php`, `app/Models/Task.php`, `app/Models/UserTask.php`

