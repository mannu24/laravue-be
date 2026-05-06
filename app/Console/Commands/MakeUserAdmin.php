<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class MakeUserAdmin extends Command
{
    protected $signature = 'user:make-admin
        {email : The email of the user to promote}
        {--password= : Set a password for admin login (required if user has no password)}
        {--create : Create the user if they don\'t exist}';

    protected $description = 'Promote a user to admin or create a new admin user';

    public function handle(): int
    {
        $email = $this->argument('email');
        $user = User::where('email', $email)->first();

        if (!$user && $this->option('create')) {
            $password = $this->option('password');
            if (!$password) {
                $password = $this->secret('Enter a password for the new admin user');
            }
            if (!$password) {
                $this->error('Password is required to create a new admin user.');
                return Command::FAILURE;
            }

            $name = $this->ask('Enter name for the admin user', 'Admin');

            $user = User::create([
                'name' => $name,
                'email' => $email,
                'username' => 'admin_' . time(),
                'password' => Hash::make($password),
                'is_admin' => true,
            ]);

            $this->info("✓ Admin user created: {$user->name} ({$email})");
            return Command::SUCCESS;
        }

        if (!$user) {
            $this->error("User with email '{$email}' not found. Use --create to create a new admin user.");
            return Command::FAILURE;
        }

        // Set password if provided
        $password = $this->option('password');
        if ($password) {
            $user->update(['password' => Hash::make($password)]);
            $this->line("Password updated.");
        }

        if ($user->is_admin) {
            $this->info("{$user->name} is already an admin.");
            return Command::SUCCESS;
        }

        $user->update(['is_admin' => true]);
        $this->info("✓ {$user->name} ({$email}) is now an admin.");

        return Command::SUCCESS;
    }
}
