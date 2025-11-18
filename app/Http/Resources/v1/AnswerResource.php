<?php

declare(strict_types=1);

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnswerResource extends JsonResource
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
            'question_id' => $this->question_id,
            'user' => $this->whenLoaded('user', fn() => $this->user ? [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ] : null),
            'body' => $this->body ?? $this->content,
            'is_ai_generated' => $this->is_ai_generated ?? false,
            'is_verified' => $this->is_verified ?? false,
            'is_accepted' => $this->is_accepted ?? false,
            'upvotes' => $this->whenLoaded('upvotes', fn() => $this->upvotes->count()),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
            'replies' => $this->whenLoaded('replies', fn() => $this->replies->map(function ($reply) use ($request) {
                return (new static($reply))->toArray($request);
            })),
        ];
    }
}
