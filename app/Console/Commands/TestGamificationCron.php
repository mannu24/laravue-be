<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestGamificationCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gamification:test-cron {job=daily : The cron job to test (daily, weekly, streaks)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test gamification cron jobs manually';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $job = $this->argument('job');

        $this->info("Testing {$job} cron job...");

        switch ($job) {
            case 'daily':
                $this->call('gamification:reset-daily-tasks');
                break;
            case 'weekly':
                $this->call('gamification:reset-weekly-tasks');
                break;
            case 'streaks':
                $this->call('gamification:check-streaks');
                break;
            case 'all':
                $this->info('Running all cron jobs...');
                $this->call('gamification:reset-daily-tasks');
                $this->newLine();
                $this->call('gamification:reset-weekly-tasks');
                $this->newLine();
                $this->call('gamification:check-streaks');
                break;
            default:
                $this->error("Invalid job: {$job}. Use 'daily', 'weekly', 'streaks', or 'all'");
                return Command::FAILURE;
        }

        $this->info("Test completed!");
        return Command::SUCCESS;
    }
}

