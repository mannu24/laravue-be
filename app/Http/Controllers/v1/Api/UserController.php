<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateProfileRequest;
use App\Http\Resources\v1\BadgeResource;
use App\Http\Resources\v1\LevelResource;
use App\Http\Resources\v1\TaskResource;
use App\Http\Resources\v1\UserResource;
use App\Http\Resources\v1\XpResource;
use App\Http\Traits\HttpResponse;
use App\Models\User;
use App\Services\User\UserService;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    use HttpResponse;

    public function __construct(
        protected UserService $userService
    ) {
    }

    /**
     * Show user profile.
     */
    public function show(string $username): JsonResponse
    {
        // Route model binding ensures user exists
        $userProfile = $this->userService->getUserProfile($username);
        
        if (!$userProfile) {
            abort(404, 'User not found');
        }
        
        return $this->success(
            data: new UserResource($userProfile->load(['level', 'badges', 'tasks'])),
            message: 'User profile retrieved successfully'
        );
    }

    /**
     * Update user profile.
     */
    public function update(UpdateProfileRequest $request, User $user): JsonResponse
    {
        $updatedUser = $this->userService->updateProfile($user, $request->validated());
        
        return $this->success(
            data: new UserResource($updatedUser),
            message: 'Profile updated successfully'
        );
    }

    /**
     * Get user gamification data.
     */
    public function gamification(User $user): JsonResponse
    {
        $data = $this->userService->getUserWithGamification($user->id);
        
        return $this->success(
            data: [
                'user' => new UserResource($data['user']),
                'xp_total' => $data['xp_total'],
                'level' => $data['level'] ? new LevelResource($data['level']) : null,
                'badges' => BadgeResource::collection($data['badges']),
                'tasks' => TaskResource::collection($data['tasks']),
                'recent_xp_logs' => XpResource::collection($data['recent_xp_logs']),
            ],
            message: 'Gamification data retrieved successfully'
        );
    }
}

