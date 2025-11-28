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
                'username' => $this->user->username ?? null,
                'profile_photo' => $this->user->profile_photo ?? null,
            ]),
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->content ?? $this->body,
            'body' => $this->body ?? $this->content,
            'ai_generated_summary' => $this->ai_generated_summary,
            'views' => $this->views ?? $this->view_count ?? 0,
            'views_count' => $this->views ?? $this->view_count ?? 0,
            'likes_count' => $this->whenLoaded('likes', fn() => $this->likes->count(), fn() => $this->likes()->count()),
            'liked' => $this->liked ?? false,
            'upvoted' => $this->upvoted ?? false,
            'comments_count' => $this->whenLoaded('answers', fn() => $this->answers->count(), fn() => $this->answers()->count()),
            'answers_count' => $this->whenLoaded('answers', fn() => $this->answers->count(), fn() => $this->answers()->count()),
            'answers' => $this->whenLoaded('answers', fn() => AnswerResource::collection($this->answers)),
            'is_verified' => $this->is_verified ?? false,
            'tags' => $this->whenLoaded('tags', fn() => TagResource::collection($this->tags)),
            'upvotes_count' => $this->whenLoaded('upvotes', fn() => $this->upvotes->count()),
            'posted_at' => $this->posted_at ?? $this->created_at?->diffForHumans(),
            'created_at' => $this->created_at?->toDateTimeString(),
            'owner' => $this->owner ?? false,
            'bookmarked' => $this->bookmarked ?? false,
            'bookmark_count' => $this->bookmark_count ?? 0,
        ];
    }
}
