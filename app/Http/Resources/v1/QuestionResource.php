<?php

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
            'title' => $this->title,
            'content' => $this->content,
            'user' => $this->user ? $this->user->only(['id', 'name', 'email']) : null,
            'upvotes_count' => $this->upvotes->count(),
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
