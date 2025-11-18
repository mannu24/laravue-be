<?php

declare(strict_types=1);

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionResource extends JsonResource
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
            'user' => $this->whenLoaded('user', fn() => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ]),
            'title' => $this->title,
            'slug' => $this->slug,
            'body' => $this->body ?? $this->content,
            'ai_generated_summary' => $this->ai_generated_summary,
            'views' => $this->views ?? $this->view_count ?? 0,
            'answers' => $this->whenLoaded('answers', fn() => AnswerResource::collection($this->answers)),
            'tags' => $this->whenLoaded('tags', fn() => TagResource::collection($this->tags)),
            'upvotes_count' => $this->whenLoaded('upvotes', fn() => $this->upvotes->count()),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
