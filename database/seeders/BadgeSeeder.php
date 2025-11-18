<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\BadgeType;
use App\Models\Badge;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BadgeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $badges = [
            // Participation Badges
            [
                'name' => 'First Login',
                'slug' => 'first-login',
                'description' => 'Welcome! You\'ve logged in for the first time.',
                'type' => BadgeType::PARTICIPATION,
                'icon_path' => '/images/badges/first-login.svg',
                'xp_reward' => 5,
            ],
            [
                'name' => 'First Question',
                'slug' => 'first-question',
                'description' => 'You asked your first question. Keep the curiosity alive!',
                'type' => BadgeType::PARTICIPATION,
                'icon_path' => '/images/badges/first-question.svg',
                'xp_reward' => 10,
            ],
            [
                'name' => 'First Answer',
                'slug' => 'first-answer',
                'description' => 'You provided your first answer. Helpful community member!',
                'type' => BadgeType::PARTICIPATION,
                'icon_path' => '/images/badges/first-answer.svg',
                'xp_reward' => 10,
            ],
            [
                'name' => 'Profile Complete',
                'slug' => 'profile-complete',
                'description' => 'You completed your profile. Great job!',
                'type' => BadgeType::PARTICIPATION,
                'icon_path' => '/images/badges/profile-complete.svg',
                'xp_reward' => 15,
            ],

            // Consistency Badges
            [
                'name' => 'Daily Streak 3',
                'slug' => 'streak-3',
                'description' => 'Maintained a 3-day login streak. Consistency is key!',
                'type' => BadgeType::CONSISTENCY,
                'icon_path' => '/images/badges/streak-3.svg',
                'xp_reward' => 20,
            ],
            [
                'name' => 'Daily Streak 7',
                'slug' => 'streak-7',
                'description' => 'Maintained a 7-day login streak. You\'re on fire!',
                'type' => BadgeType::CONSISTENCY,
                'icon_path' => '/images/badges/streak-7.svg',
                'xp_reward' => 50,
            ],
            [
                'name' => 'Daily Streak 14',
                'slug' => 'streak-14',
                'description' => 'Maintained a 14-day login streak. Impressive dedication!',
                'type' => BadgeType::CONSISTENCY,
                'icon_path' => '/images/badges/streak-14.svg',
                'xp_reward' => 100,
            ],
            [
                'name' => 'Daily Streak 30',
                'slug' => 'streak-30',
                'description' => 'Maintained a 30-day login streak. You\'re unstoppable!',
                'type' => BadgeType::CONSISTENCY,
                'icon_path' => '/images/badges/streak-30.svg',
                'xp_reward' => 200,
            ],
            [
                'name' => 'Daily Streak 60',
                'slug' => 'streak-60',
                'description' => 'Maintained a 60-day login streak. Legendary commitment!',
                'type' => BadgeType::CONSISTENCY,
                'icon_path' => '/images/badges/streak-60.svg',
                'xp_reward' => 400,
            ],
            [
                'name' => 'Daily Streak 100',
                'slug' => 'streak-100',
                'description' => 'Maintained a 100-day login streak. Absolutely incredible!',
                'type' => BadgeType::CONSISTENCY,
                'icon_path' => '/images/badges/streak-100.svg',
                'xp_reward' => 750,
            ],

            // Quality Badges
            [
                'name' => '1 Verified Answer',
                'slug' => 'verified-answer-1',
                'description' => 'You got your first answer verified. Quality content!',
                'type' => BadgeType::QUALITY,
                'icon_path' => '/images/badges/verified-1.svg',
                'xp_reward' => 25,
            ],
            [
                'name' => '5 Verified Answers',
                'slug' => 'verified-answer-5',
                'description' => 'You got 5 answers verified. You\'re a trusted expert!',
                'type' => BadgeType::QUALITY,
                'icon_path' => '/images/badges/verified-5.svg',
                'xp_reward' => 100,
            ],
            [
                'name' => '10 Verified Answers',
                'slug' => 'verified-answer-10',
                'description' => 'You got 10 answers verified. Master of quality!',
                'type' => BadgeType::QUALITY,
                'icon_path' => '/images/badges/verified-10.svg',
                'xp_reward' => 250,
            ],
            [
                'name' => '20 Verified Answers',
                'slug' => 'verified-answer-20',
                'description' => 'You got 20 answers verified. Exceptional quality!',
                'type' => BadgeType::QUALITY,
                'icon_path' => '/images/badges/verified-20.svg',
                'xp_reward' => 500,
            ],
            [
                'name' => '20 Upvoted Answers',
                'slug' => 'upvoted-20',
                'description' => 'Your answers received 20 upvotes. Community favorite!',
                'type' => BadgeType::QUALITY,
                'icon_path' => '/images/badges/upvoted-20.svg',
                'xp_reward' => 150,
            ],
            [
                'name' => '50 Upvoted Answers',
                'slug' => 'upvoted-50',
                'description' => 'Your answers received 50 upvotes. Highly appreciated!',
                'type' => BadgeType::QUALITY,
                'icon_path' => '/images/badges/upvoted-50.svg',
                'xp_reward' => 400,
            ],

            // Contribution Badges
            [
                'name' => '10 Questions Asked',
                'slug' => 'questions-10',
                'description' => 'You asked 10 questions. Curious mind!',
                'type' => BadgeType::CONTRIBUTION,
                'icon_path' => '/images/badges/questions-10.svg',
                'xp_reward' => 50,
            ],
            [
                'name' => '25 Questions Asked',
                'slug' => 'questions-25',
                'description' => 'You asked 25 questions. Always learning!',
                'type' => BadgeType::CONTRIBUTION,
                'icon_path' => '/images/badges/questions-25.svg',
                'xp_reward' => 150,
            ],
            [
                'name' => '50 Questions Asked',
                'slug' => 'questions-50',
                'description' => 'You asked 50 questions. Knowledge seeker!',
                'type' => BadgeType::CONTRIBUTION,
                'icon_path' => '/images/badges/questions-50.svg',
                'xp_reward' => 350,
            ],
            [
                'name' => '10 Answers Given',
                'slug' => 'answers-10',
                'description' => 'You provided 10 answers. Helpful contributor!',
                'type' => BadgeType::CONTRIBUTION,
                'icon_path' => '/images/badges/answers-10.svg',
                'xp_reward' => 75,
            ],
            [
                'name' => '25 Answers Given',
                'slug' => 'answers-25',
                'description' => 'You provided 25 answers. Community helper!',
                'type' => BadgeType::CONTRIBUTION,
                'icon_path' => '/images/badges/answers-25.svg',
                'xp_reward' => 200,
            ],
            [
                'name' => '50 Answers Given',
                'slug' => 'answers-50',
                'description' => 'You provided 50 answers. Generous contributor!',
                'type' => BadgeType::CONTRIBUTION,
                'icon_path' => '/images/badges/answers-50.svg',
                'xp_reward' => 450,
            ],
            [
                'name' => 'Community Helper',
                'slug' => 'community-helper',
                'description' => 'You\'ve helped the community significantly. Thank you!',
                'type' => BadgeType::CONTRIBUTION,
                'icon_path' => '/images/badges/community-helper.svg',
                'xp_reward' => 300,
            ],

            // Rare Badges
            [
                'name' => 'Level 10 Badge',
                'slug' => 'level-10',
                'description' => 'You reached Level 10. Intermediate achiever!',
                'type' => BadgeType::RARE,
                'icon_path' => '/images/badges/level-10.svg',
                'xp_reward' => 500,
            ],
            [
                'name' => 'Level 15 Badge',
                'slug' => 'level-15',
                'description' => 'You reached Level 15. Advanced player!',
                'type' => BadgeType::RARE,
                'icon_path' => '/images/badges/level-15.svg',
                'xp_reward' => 1000,
            ],
            [
                'name' => 'Level 20 Badge',
                'slug' => 'level-20',
                'description' => 'You reached Level 20. Legendary status achieved!',
                'type' => BadgeType::RARE,
                'icon_path' => '/images/badges/level-20.svg',
                'xp_reward' => 2000,
            ],
            [
                'name' => 'Perfect Week',
                'slug' => 'perfect-week',
                'description' => 'Completed all weekly tasks. Perfect execution!',
                'type' => BadgeType::RARE,
                'icon_path' => '/images/badges/perfect-week.svg',
                'xp_reward' => 300,
            ],
            [
                'name' => 'Perfect Month',
                'slug' => 'perfect-month',
                'description' => 'Completed all tasks for a month. Unstoppable!',
                'type' => BadgeType::RARE,
                'icon_path' => '/images/badges/perfect-month.svg',
                'xp_reward' => 750,
            ],

            // Event Badges
            [
                'name' => 'Early Adopter',
                'slug' => 'early-adopter',
                'description' => 'Joined during the early days. Pioneer!',
                'type' => BadgeType::EVENT,
                'icon_path' => '/images/badges/early-adopter.svg',
                'xp_reward' => 100,
            ],
        ];

        foreach ($badges as $badgeData) {
            Badge::updateOrCreate(
                ['slug' => $badgeData['slug']],
                [
                    'name' => $badgeData['name'],
                    'description' => $badgeData['description'],
                    'type' => $badgeData['type']->value,
                    'icon_path' => $badgeData['icon_path'],
                    'xp_reward' => $badgeData['xp_reward'],
                    'is_active' => true,
                ]
            );
        }

        $this->command->info('Created ' . count($badges) . ' badges.');
    }
}

