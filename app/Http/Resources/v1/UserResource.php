<?php

declare(strict_types=1);

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'email' => $this->email,
            'username' => $this->username,
            'xp_total' => $this->xp_total ?? 0,
            'level' => $this->whenLoaded('level', fn() => new LevelResource($this->level)),
            'badges' => $this->whenLoaded('badges', fn() => BadgeResource::collection($this->badges)),
            'tasks' => $this->whenLoaded('tasks', fn() => TaskResource::collection($this->tasks)),
            'streak_days' => $this->streak_days ?? 0,
            'last_active_at' => $this->last_active_at?->toDateTimeString(),
            'created_at' => $this->created_at?->toDateTimeString(),
            'profile_photo' => $this->profile_photo ?? null,
            'completed' => $this->completed ?? false,
            'is_following' => $this->is_following ?? false,
            'followers_count' => $this->followers_count ?? 0,
            'following_count' => $this->following_count ?? 0,
            'social_links' => $this->whenLoaded(
                'socialLinks',
                fn() => SocialLinkResource::collection($this->socialLinks)
            ),
        ];
    }
}

