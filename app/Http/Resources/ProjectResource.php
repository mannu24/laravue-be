<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
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
            'slug' => $this->slug,
            'description' => $this->description,
            'project_type' => $this->project_type,
            'github_url' => $this->github_url,
            'demo_url' => $this->demo_url,
            'is_sellable' => $this->is_sellable,
            'original_price' => $this->original_price,
            'selling_price' => $this->selling_price,
            'views' => $this->views,
            'is_active' => $this->is_active,
            'featured_image' => $this->featured_image,
            'upvotes_count' => $this->upvotes_count,
            'is_upvoted_by_user' => $this->is_upvoted_by_user,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Relationships
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'username' => $this->user->username,
                    'profile_photo' => $this->user->profile_photo,
                ];
            }),

            'technologies' => $this->whenLoaded('technologies', function () {
                return $this->technologies->map(function ($technology) {
                    return [
                        'id' => $technology->id,
                        'name' => $technology->name,
                    ];
                });
            }),

            'funds' => $this->whenLoaded('funds', function () {
                return $this->funds->map(function ($fund) {
                    return [
                        'id' => $fund->id,
                        'amount' => optional($fund->transaction)->amount,
                        'user' => $fund->relationLoaded('user') && $fund->user ? [
                            'id' => $fund->user->id,
                            'name' => $fund->user->name,
                            'username' => $fund->user->username,
                        ] : null,
                        'created_at' => $fund->created_at,
                    ];
                });
            }),
        ];
    }
}
