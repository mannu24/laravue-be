<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\HttpResponse;
use App\Models\Portfolio;
use App\Models\PortfolioOrder;
use App\Models\PortfolioSubscription;
use Illuminate\Http\JsonResponse;

class AdminDashboardController extends Controller
{
    use HttpResponse;

    public function stats(): JsonResponse
    {
        $activeSubscriptions = PortfolioSubscription::where('status', 'active')
            ->where('expires_at', '>', now())
            ->count();

        $totalPortfolios = Portfolio::count();
        $publishedPortfolios = Portfolio::where('is_published', true)->count();

        $revenueAllTime = PortfolioOrder::where('status', 'paid')->sum('final_amount');
        $revenueThisMonth = PortfolioOrder::where('status', 'paid')
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('final_amount');

        $totalOrders = PortfolioOrder::count();
        $paidOrders = PortfolioOrder::where('status', 'paid')->count();

        $expiringIn7Days = PortfolioSubscription::where('status', 'active')
            ->whereBetween('expires_at', [now(), now()->addDays(7)])
            ->count();

        $planDistribution = PortfolioSubscription::where('status', 'active')
            ->where('expires_at', '>', now())
            ->selectRaw('plan_id, count(*) as count')
            ->groupBy('plan_id')
            ->with('plan:id,name,slug')
            ->get()
            ->map(fn($item) => [
                'plan' => $item->plan->name ?? 'Unknown',
                'slug' => $item->plan->slug ?? '',
                'count' => $item->count,
            ]);

        $recentOrders = PortfolioOrder::with(['user:id,name,email', 'plan:id,name,slug'])
            ->latest()
            ->limit(10)
            ->get();

        return $this->success([
            'active_subscriptions' => $activeSubscriptions,
            'total_portfolios' => $totalPortfolios,
            'published_portfolios' => $publishedPortfolios,
            'revenue_all_time' => round((float) $revenueAllTime, 2),
            'revenue_this_month' => round((float) $revenueThisMonth, 2),
            'total_orders' => $totalOrders,
            'paid_orders' => $paidOrders,
            'expiring_in_7_days' => $expiringIn7Days,
            'plan_distribution' => $planDistribution,
            'recent_orders' => $recentOrders,
        ]);
    }
}
