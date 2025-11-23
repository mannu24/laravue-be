<?php

declare(strict_types=1);

namespace App\Events\Gamification;

use App\Models\Badge;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserUnlockedBadge implements ShouldBroadcast
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

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('user.' . $this->user->id);
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'gamification.badge.unlocked';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'badge' => [
                'id' => $this->badge->id,
                'name' => $this->badge->name,
                'slug' => $this->badge->slug,
                'description' => $this->badge->description,
                'type' => $this->badge->type->value ?? $this->badge->type,
                'icon_path' => $this->badge->icon_path,
                'xp_reward' => $this->badge->xp_reward,
            ],
            'payload' => $this->payload,
        ];
    }
}

