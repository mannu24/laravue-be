<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Project;
use App\Models\Technology;
use App\Models\ProjectTechnology;
use App\Models\Upvote;
use App\Models\ProjectFund;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create sample users first
        $users = User::factory(10)->create();

        // Create sample technologies
        $technologies = [
            'Laravel', 'Vue.js', 'React', 'Angular', 'Node.js', 'PHP', 'JavaScript', 'TypeScript',
            'MySQL', 'PostgreSQL', 'MongoDB', 'Redis', 'Docker', 'AWS', 'Tailwind CSS', 'Bootstrap',
            'Git', 'GitHub', 'Docker', 'Kubernetes', 'Nginx', 'Apache', 'Composer', 'NPM',
            'Webpack', 'Vite', 'Jest', 'PHPUnit', 'Cypress', 'Selenium'
        ];

        foreach ($technologies as $techName) {
            Technology::create([
                'name' => $techName,
                'created_by_id' => $users->first()->id,
                'is_active' => true
            ]);
        }

        // Create sample projects
        $projects = [
            [
                'title' => 'Laravel E-commerce Platform',
                'description' => 'A comprehensive e-commerce solution built with Laravel and Vue.js. Features include product management, order processing, payment integration, and admin dashboard.',
                'project_type' => 'open',
                'github_url' => 'https://github.com/example/laravel-ecommerce',
                'demo_url' => 'https://demo.example.com',
                'is_sellable' => false,
                'technologies' => ['Laravel', 'Vue.js', 'MySQL', 'Tailwind CSS']
            ],
            [
                'title' => 'Vue.js Admin Dashboard',
                'description' => 'Modern admin dashboard template with dark mode support, charts, and responsive design. Built with Vue 3 and includes multiple layout options.',
                'project_type' => 'open',
                'github_url' => 'https://github.com/example/vue-admin',
                'demo_url' => 'https://admin-demo.example.com',
                'is_sellable' => false,
                'technologies' => ['Vue.js', 'TypeScript', 'Tailwind CSS', 'Chart.js']
            ],
            [
                'title' => 'Laravel API Starter Kit',
                'description' => 'Production-ready Laravel API starter kit with authentication, authorization, validation, and comprehensive documentation.',
                'project_type' => 'closed',
                'github_url' => 'https://github.com/example/laravel-api',
                'demo_url' => 'https://api-demo.example.com',
                'is_sellable' => true,
                'original_price' => 99.99,
                'selling_price' => 79.99,
                'technologies' => ['Laravel', 'PHP', 'MySQL', 'JWT']
            ],
            [
                'title' => 'React Task Manager',
                'description' => 'Full-featured task management application with real-time updates, team collaboration, and project tracking capabilities.',
                'project_type' => 'open',
                'github_url' => 'https://github.com/example/react-task-manager',
                'demo_url' => 'https://tasks.example.com',
                'is_sellable' => false,
                'technologies' => ['React', 'Node.js', 'MongoDB', 'Socket.io']
            ],
            [
                'title' => 'Laravel Blog System',
                'description' => 'Complete blog system with markdown support, SEO optimization, comment system, and social sharing features.',
                'project_type' => 'closed',
                'github_url' => 'https://github.com/example/laravel-blog',
                'demo_url' => 'https://blog.example.com',
                'is_sellable' => true,
                'original_price' => 49.99,
                'selling_price' => 39.99,
                'technologies' => ['Laravel', 'PHP', 'MySQL', 'Markdown']
            ],
            [
                'title' => 'Vue.js Portfolio Template',
                'description' => 'Beautiful and responsive portfolio template built with Vue 3, featuring smooth animations and modern design.',
                'project_type' => 'closed',
                'github_url' => 'https://github.com/example/vue-portfolio',
                'demo_url' => 'https://portfolio.example.com',
                'is_sellable' => true,
                'original_price' => 29.99,
                'selling_price' => 19.99,
                'technologies' => ['Vue.js', 'CSS3', 'GSAP', 'Vite']
            ]
        ];

        foreach ($projects as $projectData) {
            $technologies = $projectData['technologies'];
            unset($projectData['technologies']);

            $project = Project::create([
                'user_id' => $users->random()->id,
                'title' => $projectData['title'],
                'description' => $projectData['description'],
                'project_type' => $projectData['project_type'],
                'github_url' => $projectData['github_url'],
                'demo_url' => $projectData['demo_url'],
                'is_sellable' => $projectData['is_sellable'],
                'original_price' => $projectData['original_price'] ?? null,
                'selling_price' => $projectData['selling_price'] ?? null,
                'views' => rand(100, 5000),
                'is_active' => true
            ]);

            // Attach technologies
            foreach ($technologies as $techName) {
                $technology = Technology::where('name', $techName)->first();
                if ($technology) {
                    ProjectTechnology::create([
                        'project_id' => $project->id,
                        'technology_id' => $technology->id
                    ]);
                }
            }

            // Add some upvotes
            $randomUsers = $users->random(rand(1, 5));
            foreach ($randomUsers as $user) {
                Upvote::create([
                    'user_id' => $user->id,
                    'record_id' => $project->id,
                    'record_type' => Project::class
                ]);
            }

            // Note: Project funding requires transactions table setup
            // Skipping funding creation for now
        }
    }
}
