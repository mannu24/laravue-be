<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\LevelTier;
use App\Models\Level;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $levels = [
            // Beginner Tier (Levels 1-5)
            ['name' => 'Level 1', 'xp_required' => 0, 'tier' => LevelTier::BEGINNER],
            ['name' => 'Level 2', 'xp_required' => 200, 'tier' => LevelTier::BEGINNER],
            ['name' => 'Level 3', 'xp_required' => 500, 'tier' => LevelTier::BEGINNER],
            ['name' => 'Level 4', 'xp_required' => 1000, 'tier' => LevelTier::BEGINNER],
            ['name' => 'Level 5', 'xp_required' => 1500, 'tier' => LevelTier::BEGINNER],

            // Intermediate Tier (Levels 6-10)
            ['name' => 'Level 6', 'xp_required' => 2200, 'tier' => LevelTier::INTERMEDIATE],
            ['name' => 'Level 7', 'xp_required' => 3000, 'tier' => LevelTier::INTERMEDIATE],
            ['name' => 'Level 8', 'xp_required' => 4000, 'tier' => LevelTier::INTERMEDIATE],
            ['name' => 'Level 9', 'xp_required' => 5200, 'tier' => LevelTier::INTERMEDIATE],
            ['name' => 'Level 10', 'xp_required' => 6600, 'tier' => LevelTier::INTERMEDIATE],

            // Advanced Tier (Levels 11-15)
            ['name' => 'Level 11', 'xp_required' => 8200, 'tier' => LevelTier::ADVANCED],
            ['name' => 'Level 12', 'xp_required' => 10000, 'tier' => LevelTier::ADVANCED],
            ['name' => 'Level 13', 'xp_required' => 12000, 'tier' => LevelTier::ADVANCED],
            ['name' => 'Level 14', 'xp_required' => 14200, 'tier' => LevelTier::ADVANCED],
            ['name' => 'Level 15', 'xp_required' => 16600, 'tier' => LevelTier::ADVANCED],

            // Expert Tier (Levels 16-19)
            ['name' => 'Level 16', 'xp_required' => 19200, 'tier' => LevelTier::EXPERT],
            ['name' => 'Level 17', 'xp_required' => 22000, 'tier' => LevelTier::EXPERT],
            ['name' => 'Level 18', 'xp_required' => 25000, 'tier' => LevelTier::EXPERT],
            ['name' => 'Level 19', 'xp_required' => 28200, 'tier' => LevelTier::EXPERT],

            // Legend Tier (Level 20+)
            ['name' => 'Level 20', 'xp_required' => 30000, 'tier' => LevelTier::LEGEND],
        ];

        foreach ($levels as $levelData) {
            Level::updateOrCreate(
                ['name' => $levelData['name']],
                [
                    'xp_required' => $levelData['xp_required'],
                    'tier' => $levelData['tier']->value,
                ]
            );
        }

        $this->command->info('Created ' . count($levels) . ' levels.');
    }
}

