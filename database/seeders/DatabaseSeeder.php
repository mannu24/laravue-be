<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Artisan::call('passport:client --personal --name="API Token"');

        // Seed gamification system data
        $this->call([
            LevelSeeder::class,
            BadgeSeeder::class,
            TaskSeeder::class,
        ]);

        // Seed portfolio system data
        $this->call([
            PortfolioTemplateSeeder::class,
            PortfolioPlanSeeder::class,
        ]);
    }
}
