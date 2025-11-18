<?php

declare(strict_types=1);

namespace App\Events\Gamification;

use App\Models\User;
use App\Models\XpLog;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserEarnedXp
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public User $user,
        public XpLog $xpLog,
        public array $payload = []
    ) {
    }
}

