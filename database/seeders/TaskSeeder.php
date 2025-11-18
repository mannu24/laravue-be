<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\TaskFrequency;
use App\Models\Task;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tasks = [
            // Daily Tasks
            [
                'title' => 'Login Today',
                'description' => 'Log in to the platform today to maintain your streak.',
                'frequency' => TaskFrequency::DAILY,
                'xp_reward' => 5,
                'is_active' => true,
            ],
            [
                'title' => 'Earn 20 XP',
                'description' => 'Earn at least 20 XP through any activity today.',
                'frequency' => TaskFrequency::DAILY,
                'xp_reward' => 10,
                'is_active' => true,
            ],
            [
                'title' => 'Answer 1 Question',
                'description' => 'Provide an answer to at least one question today.',
                'frequency' => TaskFrequency::DAILY,
                'xp_reward' => 15,
                'is_active' => true,
            ],
            [
                'title' => 'Ask 1 Question',
                'description' => 'Ask at least one question today.',
                'frequency' => TaskFrequency::DAILY,
                'xp_reward' => 10,
                'is_active' => true,
            ],
            [
                'title' => 'Complete a Task',
                'description' => 'Complete any daily task to earn bonus XP.',
                'frequency' => TaskFrequency::DAILY,
                'xp_reward' => 5,
                'is_active' => true,
            ],
            [
                'title' => 'Visit Profile',
                'description' => 'Visit your profile page today.',
                'frequency' => TaskFrequency::DAILY,
                'xp_reward' => 3,
                'is_active' => true,
            ],

            // Weekly Tasks
            [
                'title' => 'Earn 200 XP',
                'description' => 'Earn at least 200 XP throughout the week.',
                'frequency' => TaskFrequency::WEEKLY,
                'xp_reward' => 50,
                'is_active' => true,
            ],
            [
                'title' => 'Complete 10 Daily Tasks',
                'description' => 'Complete at least 10 daily tasks this week.',
                'frequency' => TaskFrequency::WEEKLY,
                'xp_reward' => 75,
                'is_active' => true,
            ],
            [
                'title' => 'Answer 5 Questions',
                'description' => 'Provide answers to at least 5 questions this week.',
                'frequency' => TaskFrequency::WEEKLY,
                'xp_reward' => 60,
                'is_active' => true,
            ],
            [
                'title' => 'Ask 3 Questions',
                'description' => 'Ask at least 3 questions this week.',
                'frequency' => TaskFrequency::WEEKLY,
                'xp_reward' => 40,
                'is_active' => true,
            ],
            [
                'title' => 'Get 1 Verified Answer',
                'description' => 'Get at least one of your answers verified this week.',
                'frequency' => TaskFrequency::WEEKLY,
                'xp_reward' => 100,
                'is_active' => true,
            ],
            [
                'title' => 'Maintain 7-Day Streak',
                'description' => 'Maintain your login streak for the entire week.',
                'frequency' => TaskFrequency::WEEKLY,
                'xp_reward' => 80,
                'is_active' => true,
            ],
        ];

        foreach ($tasks as $taskData) {
            Task::updateOrCreate(
                [
                    'title' => $taskData['title'],
                    'frequency' => $taskData['frequency']->value,
                ],
                [
                    'description' => $taskData['description'],
                    'xp_reward' => $taskData['xp_reward'],
                    'is_active' => $taskData['is_active'],
                ]
            );
        }

        $this->command->info('Created ' . count($tasks) . ' tasks.');
    }
}

