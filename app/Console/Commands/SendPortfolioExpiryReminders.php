<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\PortfolioSubscription;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class SendPortfolioExpiryReminders extends Command
{
    protected $signature = 'portfolio:send-expiry-reminders';
    protected $description = 'Send email reminders for subscriptions expiring in 7, 3, and 1 days';

    public function handle(): int
    {
        $reminderDays = [7, 3, 1];
        $sent = 0;

        foreach ($reminderDays as $days) {
            $subscriptions = PortfolioSubscription::where('status', 'active')
                ->whereDate('expires_at', now()->addDays($days)->toDateString())
                ->with('user')
                ->get();

            foreach ($subscriptions as $sub) {
                if (!$sub->user) continue;

                NotificationService::create(
                    userId: $sub->user->id,
                    type: 'portfolio_expiring',
                    title: "Portfolio subscription expires in {$days} day" . ($days > 1 ? 's' : ''),
                    message: "Your portfolio subscription expires on {$sub->expires_at->format('M d, Y')}. Renew now to keep your portfolio live.",
                    data: [
                        'url' => '/portfolio/plans',
                        'days_remaining' => $days,
                    ],
                    sendEmail: true,
                    emailBlade: 'emails.notification',
                    emailSubject: "Your LaraVue portfolio expires in {$days} day" . ($days > 1 ? 's' : ''),
                );

                $sent++;
            }
        }

        $this->info("Sent {$sent} expiry reminders.");

        return Command::SUCCESS;
    }
}
