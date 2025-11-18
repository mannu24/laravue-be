<?php

declare(strict_types=1);

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BadgeResource extends JsonResource
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
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'type' => $this->type,
            'icon_path' => $this->icon_path,
            'xp_reward' => $this->xp_reward ?? 0,
            'awarded_at' => $this->when(isset($this->pivot), fn() => $this->pivot->awarded_at?->toDateTimeString()),
        ];
    }
}

