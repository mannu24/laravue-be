<?php

declare(strict_types=1);

namespace App\Events\Gamification;

use App\Models\Level;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserLeveledUp
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public User $user,
        public Level $newLevel,
        public ?Level $previousLevel = null,
        public array $payload = []
    ) {
    }
}

