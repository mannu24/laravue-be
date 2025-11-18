<?php

declare(strict_types=1);

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'frequency' => $this->frequency,
            'xp_reward' => $this->xp_reward ?? 0,
            'status' => $this->when(isset($this->pivot), fn() => $this->pivot->status),
            'completed_at' => $this->when(isset($this->pivot), fn() => $this->pivot->completed_at?->toDateTimeString()),
            'assigned_at' => $this->when(isset($this->pivot), fn() => $this->pivot->assigned_at?->toDateTimeString()),
        ];
    }
}

