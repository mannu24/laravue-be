<?php

declare(strict_types=1);

namespace App\Events\Gamification;

use App\Models\Level;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserLeveledUp implements ShouldBroadcast
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
        return 'gamification.level.up';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'level' => [
                'id' => $this->newLevel->id,
                'name' => $this->newLevel->name,
                'xp_required' => $this->newLevel->xp_required,
                'tier' => $this->newLevel->tier->value ?? $this->newLevel->tier,
            ],
            'previous_level' => $this->previousLevel ? [
                'id' => $this->previousLevel->id,
                'name' => $this->previousLevel->name,
                'xp_required' => $this->previousLevel->xp_required,
                'tier' => $this->previousLevel->tier->value ?? $this->previousLevel->tier,
            ] : null,
            'payload' => $this->payload,
        ];
    }
}

