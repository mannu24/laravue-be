<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\TaskFrequency;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use App\Models\UserTask;
use App\Services\Gamification\TaskService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ResetDailyTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gamification:reset-daily-tasks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset daily tasks for all active users - marks incomplete tasks as expired and assigns new daily tasks';

    /**
     * Execute the console command.
     */
    public function handle(TaskService $taskService): int
    {
        $this->info('Starting daily task reset...');

        $startTime = now();
        $resetCount = 0;
        $assignedCount = 0;

        try {
            // Get all active daily tasks
            $dailyTasks = Task::where('is_active', true)
                ->where('frequency', TaskFrequency::DAILY->value)
                ->get();

            if ($dailyTasks->isEmpty()) {
                $this->warn('No active daily tasks found.');
                return Command::SUCCESS;
            }

            // Get all active users
            $users = User::whereNotNull('email_verified_at')
                ->orWhere('email_verified_at', '!=', null)
                ->get();

            $this->info("Processing {$users->count()} users...");

            DB::transaction(function () use ($users, $dailyTasks, &$resetCount, &$assignedCount) {
                foreach ($users as $user) {
                    // Reset incomplete daily tasks from yesterday
                    $yesterday = now()->subDay()->startOfDay();
                    $today = now()->startOfDay();

                    $incompleteTasks = UserTask::where('user_id', $user->id)
                        ->whereIn('task_id', $dailyTasks->pluck('id'))
                        ->where('status', TaskStatus::PENDING->value)
                        ->whereDate('assigned_at', '<', $today)
                        ->get();

                    foreach ($incompleteTasks as $userTask) {
                        // Mark as expired (we can add an expired status later, for now just update)
                        $userTask->update([
                            'assigned_at' => now(), // Reset assignment date
                        ]);
                        $resetCount++;
                    }

                    // Assign new daily tasks for today if not already assigned
                    foreach ($dailyTasks as $task) {
                        $existingTask = UserTask::where('user_id', $user->id)
                            ->where('task_id', $task->id)
                            ->whereDate('assigned_at', today())
                            ->first();

                        if (!$existingTask) {
                            UserTask::create([
                                'user_id' => $user->id,
                                'task_id' => $task->id,
                                'assigned_at' => now(),
                                'status' => TaskStatus::PENDING->value,
                            ]);
                            $assignedCount++;
                        }
                    }
                }
            });

            $duration = now()->diffInSeconds($startTime);

            $this->info("Daily task reset completed!");
            $this->info("Reset {$resetCount} incomplete tasks");
            $this->info("Assigned {$assignedCount} new daily tasks");
            $this->info("Duration: {$duration} seconds");

            Log::info('Daily task reset completed', [
                'reset_count' => $resetCount,
                'assigned_count' => $assignedCount,
                'duration_seconds' => $duration,
            ]);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Error resetting daily tasks: {$e->getMessage()}");
            Log::error('Daily task reset failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return Command::FAILURE;
        }
    }
}

