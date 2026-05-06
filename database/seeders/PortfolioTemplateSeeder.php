<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\PortfolioTemplate;
use Illuminate\Database\Seeder;

class PortfolioTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Minimal',
                'slug' => 'minimal',
                'description' => 'Clean, white-space-heavy, typography-focused. A single-page scroll layout that lets your work speak for itself.',
                'is_active' => true,
                'is_premium' => false,
                'sort_order' => 1,
            ],
            [
                'name' => 'Developer',
                'slug' => 'developer',
                'description' => 'Dark theme with terminal-inspired aesthetics. Monospace fonts, code-block styling, and a hacker-friendly vibe.',
                'is_active' => true,
                'is_premium' => false,
                'sort_order' => 2,
            ],
        ];

        foreach ($templates as $template) {
            PortfolioTemplate::updateOrCreate(
                ['slug' => $template['slug']],
                $template
            );
        }

        $this->command->info('Created ' . count($templates) . ' portfolio templates.');
    }
}
