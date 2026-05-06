<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\PortfolioPlan;
use Illuminate\Database\Seeder;

class PortfolioPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'duration_months' => 3,
                'price' => 299.00,
                'max_projects' => 5,
                'allows_custom_domain' => false,
                'features' => [
                    '1 template',
                    '5 portfolio projects',
                    'Basic analytics',
                    'LaraVue subdomain',
                ],
                'sort_order' => 1,
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro',
                'duration_months' => 6,
                'price' => 499.00,
                'max_projects' => 15,
                'allows_custom_domain' => true,
                'features' => [
                    'All templates',
                    '15 portfolio projects',
                    'Analytics & custom SEO',
                    'Custom domain support',
                    'Custom sections',
                ],
                'sort_order' => 2,
            ],
            [
                'name' => 'Annual',
                'slug' => 'annual',
                'duration_months' => 12,
                'price' => 799.00,
                'max_projects' => null, // unlimited
                'allows_custom_domain' => true,
                'features' => [
                    'All templates',
                    'Unlimited projects',
                    'Analytics & custom SEO',
                    'Custom domain support',
                    'Custom sections',
                    'Priority support',
                    'Exclusive badge',
                ],
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $plan) {
            PortfolioPlan::updateOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }

        $this->command->info('Created ' . count($plans) . ' portfolio plans.');
    }
}
