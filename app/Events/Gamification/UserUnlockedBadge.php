<?php

declare(strict_types=1);

namespace App\Events\Gamification;

use App\Models\Badge;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserUnlockedBadge
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public User $user,
        public Badge $badge,
        public array $payload = []
    ) {
    }
}

