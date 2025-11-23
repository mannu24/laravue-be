<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Badge;
use App\Models\Level;
use App\Models\Question;
use App\Models\Answer;
use App\Models\Task;
use App\Models\User;
use App\Models\UserTask;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get first level
        $firstLevel = Level::where('xp_required', 0)->first();

        $users = [
            [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'username' => 'johndoe',
                'password' => Hash::make('password'),
                'xp_total' => 1500,
                'level_id' => $firstLevel?->id,
                'streak_days' => 5,
                'last_active_at' => now(),
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'username' => 'janesmith',
                'password' => Hash::make('password'),
                'xp_total' => 3200,
                'level_id' => $firstLevel?->id,
                'streak_days' => 12,
                'last_active_at' => now()->subHours(2),
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Bob Johnson',
                'email' => 'bob@example.com',
                'username' => 'bobjohnson',
                'password' => Hash::make('password'),
                'xp_total' => 850,
                'level_id' => $firstLevel?->id,
                'streak_days' => 2,
                'last_active_at' => now()->subDays(1),
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Alice Williams',
                'email' => 'alice@example.com',
                'username' => 'alicewilliams',
                'password' => Hash::make('password'),
                'xp_total' => 5200,
                'level_id' => $firstLevel?->id,
                'streak_days' => 25,
                'last_active_at' => now()->subMinutes(30),
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Charlie Brown',
                'email' => 'charlie@example.com',
                'username' => 'charliebrown',
                'password' => Hash::make('password'),
                'xp_total' => 1200,
                'level_id' => $firstLevel?->id,
                'streak_days' => 0,
                'last_active_at' => now()->subDays(3),
                'email_verified_at' => now(),
            ],
        ];

        $createdUsers = [];

        foreach ($users as $userData) {
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
            $createdUsers[] = $user;
        }

        // Assign random badges
        $badges = Badge::all();
        foreach ($createdUsers as $user) {
            $randomBadges = $badges->random(rand(2, 5));
            foreach ($randomBadges as $badge) {
                if (!$user->badges()->where('badge_id', $badge->id)->exists()) {
                    $user->badges()->attach($badge->id, [
                        'awarded_at' => now()->subDays(rand(1, 30)),
                    ]);
                }
            }
        }

        // Assign some tasks
        $tasks = Task::all();
        foreach ($createdUsers as $user) {
            $randomTasks = $tasks->random(rand(3, 6));
            foreach ($randomTasks as $task) {
                if (!$user->tasks()->where('task_id', $task->id)->exists()) {
                    UserTask::create([
                        'user_id' => $user->id,
                        'task_id' => $task->id,
                        'assigned_at' => now()->subDays(rand(0, 7)),
                        'status' => rand(0, 1) ? 'completed' : 'pending',
                        'completed_at' => rand(0, 1) ? now()->subDays(rand(0, 3)) : null,
                    ]);
                }
            }
        }

        // Create sample questions and answers
        foreach ($createdUsers as $user) {
            // Create 2-4 questions per user
            for ($i = 0; $i < rand(2, 4); $i++) {
                $questionBody = 'This is a sample question created for testing purposes. ' . 'It demonstrates how questions appear in the system.';
                Question::create([
                    'user_id' => $user->id,
                    'title' => "Sample Question " . ($i + 1) . " by {$user->name}",
                    'slug' => 'sample-question-' . $user->id . '-' . ($i + 1),
                    'content' => $questionBody,
                    'body' => $questionBody,
                    'views' => rand(10, 100),
                    'is_solved' => rand(0, 1),
                    'score' => rand(0, 10),
                    'created_at' => now()->subDays(rand(1, 30)),
                ]);
            }

            // Create 1-3 answers per user
            $allQuestions = Question::all();
            for ($i = 0; $i < rand(1, 3); $i++) {
                if ($allQuestions->isNotEmpty()) {
                    $randomQuestion = $allQuestions->random();
                    $answerBody = 'This is a sample answer created for testing purposes. ' . 'It demonstrates how answers appear in the system.';
                    Answer::create([
                        'question_id' => $randomQuestion->id,
                        'user_id' => $user->id,
                        'content' => $answerBody,
                        'body' => $answerBody,
                        'is_verified' => rand(0, 1),
                        'is_accepted' => rand(0, 1),
                        'score' => rand(0, 15),
                        'created_at' => now()->subDays(rand(1, 20)),
                    ]);
                }
            }
        }

        $this->command->info('Created ' . count($createdUsers) . ' test users with badges, tasks, and Q&A content.');
    }
}

