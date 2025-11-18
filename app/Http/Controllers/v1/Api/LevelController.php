<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\LevelResource;
use App\Http\Traits\HttpResponse;
use App\Models\User;
use App\Services\Gamification\LevelService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LevelController extends Controller
{
    use HttpResponse;

    public function __construct(
        protected LevelService $levelService
    ) {
    }

    /**
     * Get level for given XP amount.
     */
    public function show(Request $request): JsonResponse
    {
        $request->validate([
            'xp' => 'required|integer|min:0'
        ]);
        
        $xp = (int) $request->input('xp');
        $level = $this->levelService->getCurrentLevel($xp);
        
        if (!$level) {
            abort(404, 'No level found for this XP amount');
        }
        
        return $this->success(
            data: new LevelResource($level),
            message: 'Level retrieved successfully'
        );
    }

    /**
     * Get level progress for a user.
     */
    public function progress(User $user): JsonResponse
    {
        $progress = $this->levelService->getLevelProgress($user);
        
        return $this->success(
            data: $progress,
            message: 'Level progress retrieved successfully'
        );
    }
}
