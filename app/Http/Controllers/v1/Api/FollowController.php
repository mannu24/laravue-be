<?php

namespace App\Http\Controllers\v1\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\HttpResponse;
use App\Models\Follower;
use App\Models\Notification;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FollowController extends Controller
{
    use HttpResponse;

    /**
     * Toggle follow/unfollow a user
     * Single API endpoint that handles both follow and unfollow
     * 
     * @param Request $request
     * @param string $username Username of the user to follow/unfollow
     * @return JsonResponse
     */
    public function toggle(Request $request, string $username): JsonResponse
    {
        try {
            $authUser = auth()->guard('api')->user();
            
            if (!$authUser) {
                return $this->error('Unauthorized', 401);
            }

            // Prevent users from following themselves
            if ($authUser->username === $username) {
                return $this->error('You cannot follow yourself', 400);
            }

            // Find the user to follow/unfollow
            $userToFollow = User::where('username', $username)->first();

            if (!$userToFollow) {
                return $this->error('User not found', 404);
            }

            DB::beginTransaction();

            // Check if already following
            $isFollowing = Follower::where('follower_id', $authUser->id)
                ->where('following_id', $userToFollow->id)
                ->exists();

            if ($isFollowing) {
                // Unfollow
                // Check if follow happened less than 2 minutes ago and delete notification if so
                NotificationService::deleteRecentFollowNotification(
                    userId: $userToFollow->id,
                    notifiableId: $authUser->id,
                    cooldownMinutes: 2
                );

                Follower::where('follower_id', $authUser->id)
                    ->where('following_id', $userToFollow->id)
                    ->delete();

                $action = 'unfollowed';
                $isFollowingNow = false;
            } else {
                // Follow
                // Check if there's a recent follow notification (within 2 minutes)
                // This handles the case where user follows, unfollows, and follows again quickly
                $hasRecentNotification = NotificationService::hasRecentFollowNotification(
                    userId: $userToFollow->id,
                    notifiableId: $authUser->id,
                    cooldownMinutes: 2
                );

                $follower = Follower::create([
                    'follower_id' => $authUser->id,
                    'following_id' => $userToFollow->id,
                ]);

                // Only send notification if there's no recent notification (within 2 minutes)
                if (!$hasRecentNotification) {
                    $profileUrl = "/@{$userToFollow->username}";
                    NotificationService::create(
                        userId: $userToFollow->id,
                        type: Notification::TYPE_FOLLOWED,
                        title: 'New follower',
                        message: $authUser->name . ' started following you',
                        subject: $follower,
                        notifiableId: $authUser->id,
                        data: ['url' => $profileUrl],
                        sendEmail: true,
                        emailBlade: 'emails.notification',
                        emailSubject: 'You got a new follower'
                    );
                }

                // Activity is tracked automatically via Follower model boot method
                $action = 'followed';
                $isFollowingNow = true;
            }

            DB::commit();

            // Refresh user to get updated counts
            $userToFollow->refresh();

            return $this->success(
                data: [
                    'is_following' => $isFollowingNow,
                    'action' => $action,
                    'user' => [
                        'id' => $userToFollow->id,
                        'username' => $userToFollow->username,
                        'name' => $userToFollow->name,
                        'followers_count' => $userToFollow->followers_count,
                        'following_count' => $userToFollow->following_count,
                    ]
                ],
                message: "Successfully {$action} {$userToFollow->username}"
            );

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Follow toggle error', [
                'user_id' => auth()->guard('api')->id(),
                'username' => $username,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->internalError(
                message: 'An error occurred while processing your request'
            );
        }
    }

    /**
     * Get followers list for a user
     * 
     * @param Request $request
     * @param string $username
     * @return JsonResponse
     */
    public function followers(Request $request, string $username): JsonResponse
    {
        try {
            $user = User::where('username', $username)->firstOrFail();

            $perPage = min((int)$request->input('per_page', 20), 100);
            $page = max((int)$request->input('page', 1), 1);

            $followers = $user->followers()
                ->select('users.id', 'users.name', 'users.username', 'users.email')
                ->withCount(['followers', 'following'])
                ->orderBy('followers.created_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            // Transform followers data
            $followersData = $followers->map(function ($follower) {
                return [
                    'id' => $follower->id,
                    'name' => $follower->name,
                    'username' => $follower->username,
                    'avatar' => $follower->profile_photo,
                    'followers_count' => $follower->followers_count,
                    'following_count' => $follower->following_count,
                    'is_following' => auth()->guard('api')->check() 
                        ? auth()->guard('api')->user()->following()->where('following_id', $follower->id)->exists()
                        : false,
                    'followed_at' => $follower->pivot->created_at->diffForHumans(),
                ];
            });

            return $this->success(
                data: [
                    'followers' => $followersData,
                    'pagination' => [
                        'current_page' => $followers->currentPage(),
                        'per_page' => $followers->perPage(),
                        'total' => $followers->total(),
                        'last_page' => $followers->lastPage(),
                    ]
                ],
                message: 'Followers retrieved successfully'
            );

        } catch (\Exception $e) {
            Log::error('Get followers error', [
                'username' => $username,
                'error' => $e->getMessage()
            ]);

            return $this->internalError(
                message: 'Failed to retrieve followers'
            );
        }
    }

    /**
     * Get following list for a user
     * 
     * @param Request $request
     * @param string $username
     * @return JsonResponse
     */
    public function following(Request $request, string $username): JsonResponse
    {
        try {
            $user = User::where('username', $username)->firstOrFail();

            $perPage = min((int)$request->input('per_page', 20), 100);
            $page = max((int)$request->input('page', 1), 1);

            $following = $user->following()
                ->select('users.id', 'users.name', 'users.username', 'users.email')
                ->withCount(['followers', 'following'])
                ->orderBy('followers.created_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            // Transform following data
            $followingData = $following->map(function ($followedUser) {
                return [
                    'id' => $followedUser->id,
                    'name' => $followedUser->name,
                    'username' => $followedUser->username,
                    'avatar' => $followedUser->profile_photo,
                    'followers_count' => $followedUser->followers_count,
                    'following_count' => $followedUser->following_count,
                    'is_following' => true, // User is following this person
                    'followed_at' => $followedUser->pivot->created_at->diffForHumans(),
                ];
            });

            return $this->success(
                data: [
                    'following' => $followingData,
                    'pagination' => [
                        'current_page' => $following->currentPage(),
                        'per_page' => $following->perPage(),
                        'total' => $following->total(),
                        'last_page' => $following->lastPage(),
                    ]
                ],
                message: 'Following list retrieved successfully'
            );

        } catch (\Exception $e) {
            Log::error('Get following error', [
                'username' => $username,
                'error' => $e->getMessage()
            ]);

            return $this->internalError(
                message: 'Failed to retrieve following list'
            );
        }
    }

    /**
     * Check if authenticated user is following a specific user
     * 
     * @param string $username
     * @return JsonResponse
     */
    public function check(string $username): JsonResponse
    {
        try {
            $authUser = auth()->guard('api')->user();
            
            if (!$authUser) {
                return $this->error('Unauthorized', 401);
            }

            $user = User::where('username', $username)->firstOrFail();

            $isFollowing = $authUser->following()
                ->where('following_id', $user->id)
                ->exists();

            return $this->success(
                data: [
                    'is_following' => $isFollowing,
                    'user' => [
                        'id' => $user->id,
                        'username' => $user->username,
                        'followers_count' => $user->followers_count,
                        'following_count' => $user->following_count,
                    ]
                ],
                message: 'Follow status retrieved successfully'
            );

        } catch (\Exception $e) {
            return $this->internalError(
                message: 'Failed to check follow status'
            );
        }
    }
}

