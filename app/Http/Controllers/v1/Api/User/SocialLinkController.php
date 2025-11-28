<?php

namespace App\Http\Controllers\v1\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\v1\User\Profile\SocialLinkRequest;
use App\Http\Resources\v1\SocialLinkResource;
use App\Models\SocialLinkType;
use App\Models\UserSocialLink;

class SocialLinkController extends Controller
{
    public function index()
    {
        return SocialLinkResource::collection(auth()->user()->socialLinks);
    }

    public function store(SocialLinkRequest $request)
    {
        $socialLink = auth()->user()->socialLinks()->create([
            'social_link_type_id' => $request->social_link_type_id,
            'username' => $request->username,
            'url' => trim($request->username, '/'),
            'position' => $request->position ?? auth()->user()->socialLinks()->count(),
            'is_visible' => $request->is_visible ?? true,
        ]);

        return new SocialLinkResource($socialLink);
    }

    public function update(SocialLinkRequest $request, UserSocialLink $socialLink)
    {
        $socialLink->update([
            'social_link_type_id' => $request->social_link_type_id,
            'username' => $request->username,
            'url' => trim($request->username, '/'),
            'position' => $request->position ?? $socialLink->position,
            'is_visible' => $request->is_visible ?? $socialLink->is_visible,
        ]);

        return new SocialLinkResource($socialLink);
    }

    public function destroy(UserSocialLink $socialLink)
    {
        $socialLink->delete();
        return response()->json(['message' => 'Social link deleted']);
    }

    public function types()
    {
        return response()->json(SocialLinkType::where('is_active', true)->get());
    }
}
