<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Gamification Cron Jobs
Schedule::command('gamification:reset-daily-tasks')
    ->dailyAt('00:00')
    ->timezone('UTC')
    ->description('Reset daily tasks for all users')
    ->withoutOverlapping()
    ->runInBackground();

Schedule::command('gamification:reset-weekly-tasks')
    ->weeklyOn(1, '00:00') // Monday at midnight
    ->timezone('UTC')
    ->description('Reset weekly tasks for all users')
    ->withoutOverlapping()
    ->runInBackground();

Schedule::command('gamification:check-streaks')
    ->dailyAt('01:00')
    ->timezone('UTC')
    ->description('Check and update user streaks based on activity')
    ->withoutOverlapping()
    ->runInBackground();
