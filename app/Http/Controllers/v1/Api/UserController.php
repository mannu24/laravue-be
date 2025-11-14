<?php

namespace App\Http\Controllers\v1\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Traits\HttpResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserController extends Controller
{
    use HttpResponse;

    public function user(): JsonResponse
    {
        $data = [
            'user' => auth()->guard('api')->user()
        ];

        return $this->success(
            data: $data
        );
    }

    /**
     * Get user profile by username
     */
    public function show(string $username): JsonResponse
    {
        try {
            $user = \App\Models\User::where('username', $username)
                ->with(['socialLinks'])
                ->firstOrFail();

            // Check if authenticated user is following this user
            $isFollowing = false;
            if (auth()->guard('api')->check()) {
                $isFollowing = $user->followers()
                    ->where('follower_id', auth()->guard('api')->id())
                    ->exists();
            }

            $data = [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'email' => $user->email,
                    'profile_photo' => $user->profile_photo,
                    'completed' => $user->completed,
                    'followers_count' => $user->followers_count,
                    'following_count' => $user->following_count,
                    'is_following' => $isFollowing,
                    'created_at' => $user->created_at,
                    'social_links' => $user->socialLinks,
                ]
            ];

            return $this->success(
                data: $data,
                message: 'User profile retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->error(
                message: 'User not found',
                statusCode: 404
            );
        }
    }

    public function update(UpdateUserRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $user = request()->user();
            $user->update([
                'name' => $request->name,
                'username' => $request->username,
            ]);
            if ($request->hasFile('profile_photo')) {
                $user->clearMediaCollection('profile_photo');
                $user->addMedia($request->file('profile_photo'))
                    ->usingFileName(Str::random(15) . '.' . $request->file('profile_photo')->getClientOriginalExtension())
                    ->toMediaCollection('profile_photo');
            }

            DB::commit();

            $data = [
                'user' => $user
            ];

            return $this->success(
                data: $data
            );
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->internalError(
                message: "An error occurred: " . $e->getMessage()
            );
        }
    }
}
