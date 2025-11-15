<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

trait TracksViewsWithRateLimit
{
    /**
     * Track a view with IP-based rate limiting
     * 
     * @param string $type Type of content (e.g., 'project', 'post')
     * @param int $id Content ID
     * @param Request $request
     * @param int $rateLimitMinutes Rate limit in minutes (default: 5)
     * @return bool Returns true if view was counted, false if rate limited
     */
    protected function trackViewWithRateLimit(
        string $type,
        int $id,
        Request $request,
        int $rateLimitMinutes = 5
    ): bool {
        $ipAddress = $request->ip();
        $cacheKey = "view:{$type}:{$id}:{$ipAddress}";
        
        // Check if this IP has viewed this content within the rate limit period
        if (Cache::has($cacheKey)) {
            return false; // Rate limited, don't count this view
        }
        
        // Set cache to prevent duplicate views for the rate limit period
        Cache::put($cacheKey, true, now()->addMinutes($rateLimitMinutes));
        
        return true; // View should be counted
    }
}

