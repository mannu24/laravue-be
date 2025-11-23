<?php

declare(strict_types=1);

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SocialLinkResource extends JsonResource
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
            'username' => $this->username,
            'url' => $this->url,
            'position' => $this->position,
            'clicks' => $this->clicks ?? 0,
            'is_visible' => (bool) ($this->is_visible ?? true),
            'social_link_type' => $this->whenLoaded('socialLinkType', function () {
                return [
                    'id' => $this->socialLinkType->id,
                    'name' => $this->socialLinkType->name,
                    'icon' => $this->socialLinkType->icon,
                    'base_url' => $this->socialLinkType->base_url,
                ];
            }),
        ];
    }
}
