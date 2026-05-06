<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Portfolio;
use App\Models\PortfolioSubscription;
use Illuminate\Console\Command;

class ExpirePortfolioSubscriptions extends Command
{
    protected $signature = 'portfolio:expire-subscriptions';
    protected $description = 'Expire portfolio subscriptions past their expiry date and start grace period';

    public function handle(): int
    {
        // Find active subscriptions that have expired but grace period hasn't ended
        $expired = PortfolioSubscription::where('status', 'active')
            ->where('expires_at', '<=', now())
            ->where(function ($q) {
                $q->whereNull('grace_ends_at')
                  ->orWhere('grace_ends_at', '>', now());
            })
            ->get();

        $this->info("Found {$expired->count()} subscriptions in grace period.");

        // Find subscriptions past grace period — mark as expired and unpublish
        $pastGrace = PortfolioSubscription::where('status', 'active')
            ->where('expires_at', '<=', now())
            ->where('grace_ends_at', '<=', now())
            ->get();

        foreach ($pastGrace as $sub) {
            $sub->update(['status' => 'expired']);

            Portfolio::where('user_id', $sub->user_id)
                ->where('is_published', true)
                ->update(['is_published' => false]);

            $this->line("Expired subscription #{$sub->id} for user #{$sub->user_id} — portfolio unpublished.");
        }

        $this->info("Expired {$pastGrace->count()} subscriptions past grace period.");

        return Command::SUCCESS;
    }
}
