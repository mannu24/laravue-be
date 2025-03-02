<?php

namespace App\Http\Controllers\v1\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\v1\User\Profile\SocialLinkRequest;
use App\Http\Resources\v1\SocialLinkResource;
use App\Models\SocialLinkType;
use App\Models\UserSocialLink;
use Illuminate\Http\Request;

class SocialLinkController extends Controller
{
    public function index()
    {
        return SocialLinkResource::collection(auth()->user()->socialLinks);
    }

    public function store(SocialLinkRequest $request)
    {
        $socialLinkType = SocialLinkType::findOrFail($request->social_link_type_id);

        $url = $socialLinkType->base_url
            ? rtrim($socialLinkType->base_url, '/') . '/' . trim($request->username, '/')
            : $request->username;

        $socialLink = auth()->user()->socialLinks()->create([
            'social_link_type_id' => $request->social_link_type_id,
            'username' => $request->username,
            'url' => $url,
            'position' => $request->position ?? auth()->user()->socialLinks()->count(),
            'is_visible' => $request->is_visible ?? true,
        ]);

        return new SocialLinkResource($socialLink);
    }

    public function update(SocialLinkRequest $request, UserSocialLink $socialLink)
    {
        $this->authorize('update', $socialLink);

        $socialLinkType = SocialLinkType::findOrFail($request->social_link_type_id);
        $url = $socialLinkType->base_url
            ? rtrim($socialLinkType->base_url, '/') . '/' . trim($request->username, '/')
            : $request->username;

        $socialLink->update([
            'social_link_type_id' => $request->social_link_type_id,
            'username' => $request->username,
            'url' => $url,
            'position' => $request->position ?? $socialLink->position,
            'is_visible' => $request->is_visible ?? $socialLink->is_visible,
        ]);

        return new SocialLinkResource($socialLink);
    }

    public function destroy(UserSocialLink $socialLink)
    {
        $this->authorize('delete', $socialLink);
        $socialLink->delete();
        return response()->json(['message' => 'Social link deleted']);
    }

    public function types()
    {
        return response()->json(SocialLinkType::where('is_active', true)->get());
    }
}
