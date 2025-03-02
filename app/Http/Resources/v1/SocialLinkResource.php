<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SocialLinkResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'social_link_type' => [
                'id' => $this->socialLinkType->id,
                'name' => $this->socialLinkType->name,
                'icon' => $this->socialLinkType->icon,
            ],
            'username' => $this->username,
            'url' => $this->url,
            'position' => $this->position,
            'clicks' => $this->clicks,
            'is_visible' => $this->is_visible,
            'created_at' => $this->created_at,
        ];
    }
}
