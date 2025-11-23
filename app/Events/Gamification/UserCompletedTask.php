<?php

declare(strict_types=1);

namespace App\Events\Gamification;

use App\Models\Task;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserCompletedTask implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public User $user,
        public Task $task,
        public array $payload = []
    ) {
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('user.' . $this->user->id);
    }

    public function broadcastAs(): string
    {
        return 'gamification.task.completed';
    }

    public function broadcastWith(): array
    {
        // Get XP reward from payload (which should have the actual awarded amount)
        // This is the amount that was actually awarded, not just the task's default
        $xpReward = $this->payload['xp_reward'] ?? $this->task->xp_reward ?? 0;
        
        return [
            'task' => [
                'id' => $this->task->id,
                'title' => $this->task->title,
                'frequency' => $this->task->frequency->value ?? $this->task->frequency,
                'xp_reward' => $this->task->xp_reward ?? 0,
            ],
            'xp_reward' => $xpReward, // Use the actual XP amount awarded
            'source' => $this->payload['source'] ?? 'manual',
            'payload' => $this->payload,
        ];
    }
}

