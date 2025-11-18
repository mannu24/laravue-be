<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Gamification\AssignBadgeRequest;
use App\Http\Resources\v1\BadgeResource;
use App\Http\Traits\HttpResponse;
use App\Models\Badge;
use App\Models\User;
use App\Services\Gamification\BadgeService;
use Illuminate\Http\JsonResponse;

class BadgeController extends Controller
{
    use HttpResponse;

    public function __construct(
        protected BadgeService $badgeService
    ) {
    }

    /**
     * Get all badges.
     */
    public function index(): JsonResponse
    {
        $badges = $this->badgeService->getAllBadges();
        
        return $this->success(
            data: BadgeResource::collection($badges),
            message: 'Badges retrieved successfully'
        );
    }

    /**
     * Get badges for a user.
     */
    public function userBadges(User $user): JsonResponse
    {
        $badges = $this->badgeService->getBadgesForUser($user->id);
        
        return $this->success(
            data: BadgeResource::collection($badges),
            message: 'User badges retrieved successfully'
        );
    }

    /**
     * Award badge to user (admin feature).
     */
    public function award(AssignBadgeRequest $request): JsonResponse
    {
        $user = User::findOrFail($request->user_id);
        $badge = Badge::findOrFail($request->badge_id);
        
        $this->badgeService->awardBadge($user, $badge);
        
        return $this->success(
            data: new BadgeResource($badge),
            message: 'Badge awarded successfully'
        );
    }
}
