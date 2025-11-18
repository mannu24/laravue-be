<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\TaskFrequency;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use App\Models\UserTask;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ResetWeeklyTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gamification:reset-weekly-tasks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset weekly tasks for all active users - marks incomplete tasks as expired and assigns new weekly tasks';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting weekly task reset...');

        $startTime = now();
        $resetCount = 0;
        $assignedCount = 0;

        try {
            // Get all active weekly tasks
            $weeklyTasks = Task::where('is_active', true)
                ->where('frequency', TaskFrequency::WEEKLY->value)
                ->get();

            if ($weeklyTasks->isEmpty()) {
                $this->warn('No active weekly tasks found.');
                return Command::SUCCESS;
            }

            // Get all active users
            $users = User::whereNotNull('email_verified_at')
                ->orWhere('email_verified_at', '!=', null)
                ->get();

            $this->info("Processing {$users->count()} users...");

            $weekStart = now()->startOfWeek();
            $lastWeekStart = now()->subWeek()->startOfWeek();

            DB::transaction(function () use ($users, $weeklyTasks, $weekStart, $lastWeekStart, &$resetCount, &$assignedCount) {
                foreach ($users as $user) {
                    // Reset incomplete weekly tasks from last week
                    $incompleteTasks = UserTask::where('user_id', $user->id)
                        ->whereIn('task_id', $weeklyTasks->pluck('id'))
                        ->where('status', TaskStatus::PENDING->value)
                        ->where('assigned_at', '<', $weekStart)
                        ->get();

                    foreach ($incompleteTasks as $userTask) {
                        // Reset assignment date for new week
                        $userTask->update([
                            'assigned_at' => now(),
                        ]);
                        $resetCount++;
                    }

                    // Assign new weekly tasks for this week if not already assigned
                    foreach ($weeklyTasks as $task) {
                        $existingTask = UserTask::where('user_id', $user->id)
                            ->where('task_id', $task->id)
                            ->whereBetween('assigned_at', [$weekStart, now()->endOfWeek()])
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

            $this->info("Weekly task reset completed!");
            $this->info("Reset {$resetCount} incomplete tasks");
            $this->info("Assigned {$assignedCount} new weekly tasks");
            $this->info("Duration: {$duration} seconds");

            Log::info('Weekly task reset completed', [
                'reset_count' => $resetCount,
                'assigned_count' => $assignedCount,
                'duration_seconds' => $duration,
            ]);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Error resetting weekly tasks: {$e->getMessage()}");
            Log::error('Weekly task reset failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return Command::FAILURE;
        }
    }
}

