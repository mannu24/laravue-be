<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\XpResource;
use App\Http\Traits\HttpResponse;
use App\Models\User;
use App\Services\Gamification\XpService;
use Illuminate\Http\JsonResponse;

class XpController extends Controller
{
    use HttpResponse;

    public function __construct(
        protected XpService $xpService
    ) {
    }

    /**
     * Get XP logs for a user.
     */
    public function index(User $user): JsonResponse
    {
        $logs = $this->xpService->getXpHistory($user->id);
        
        return $this->success(
            data: XpResource::collection($logs),
            message: 'XP logs retrieved successfully'
        );
    }

    /**
     * Get XP summary for a user.
     */
    public function summary(User $user): JsonResponse
    {
        $summary = $this->xpService->getUserXpSummary($user);
        
        return $this->success(
            data: $summary,
            message: 'XP summary retrieved successfully'
        );
    }
}
