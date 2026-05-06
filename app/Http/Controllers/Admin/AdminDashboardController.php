<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Portfolio;
use App\Models\PortfolioCoupon;
use App\Models\PortfolioOrder;
use App\Models\PortfolioPlan;
use App\Models\PortfolioSubscription;
use App\Models\PortfolioTemplate;
use App\Services\RazorpayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'active_subscriptions' => PortfolioSubscription::where('status', 'active')->where('expires_at', '>', now())->count(),
            'total_portfolios' => Portfolio::count(),
            'published_portfolios' => Portfolio::where('is_published', true)->count(),
            'revenue_all_time' => round((float) PortfolioOrder::where('status', 'paid')->sum('final_amount'), 2),
            'revenue_this_month' => round((float) PortfolioOrder::where('status', 'paid')->whereMonth('paid_at', now()->month)->whereYear('paid_at', now()->year)->sum('final_amount'), 2),
            'total_orders' => PortfolioOrder::count(),
            'paid_orders' => PortfolioOrder::where('status', 'paid')->count(),
            'expiring_in_7_days' => PortfolioSubscription::where('status', 'active')->whereBetween('expires_at', [now(), now()->addDays(7)])->count(),
        ];

        $recentOrders = PortfolioOrder::with(['user:id,name,email', 'plan:id,name,slug'])
            ->latest()->limit(10)->get();

        return view('admin.dashboard', compact('stats', 'recentOrders'));
    }

    // --- Portfolios ---
    public function portfolios(Request $request)
    {
        $query = Portfolio::with(['user:id,name,email,username'])->withCount('projects');

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('subdomain', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
            });
        }

        $portfolios = $query->latest()->paginate(20)->withQueryString();
        return view('admin.portfolios', compact('portfolios'));
    }

    public function togglePublish(int $id)
    {
        $portfolio = Portfolio::findOrFail($id);
        $portfolio->update(['is_published' => !$portfolio->is_published]);
        return back()->with('success', $portfolio->is_published ? 'Portfolio published.' : 'Portfolio unpublished.');
    }

    // --- Plans ---
    public function plans()
    {
        $plans = PortfolioPlan::orderBy('sort_order')->get();
        return view('admin.plans', compact('plans'));
    }

    public function updatePlan(Request $request, int $id)
    {
        $plan = PortfolioPlan::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'duration_months' => 'required|integer|min:1',
            'max_projects' => 'nullable|integer|min:1',
            'allows_custom_domain' => 'sometimes',
            'is_active' => 'sometimes',
        ]);
        $validated['allows_custom_domain'] = $request->has('allows_custom_domain');
        $validated['is_active'] = $request->has('is_active');
        $plan->update($validated);
        return back()->with('success', 'Plan updated.');
    }

    // --- Coupons ---
    public function coupons()
    {
        $coupons = PortfolioCoupon::latest()->paginate(20);
        return view('admin.coupons', compact('coupons'));
    }

    public function storeCoupon(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|min:3|max:20|unique:portfolio_coupons,code',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0.01',
            'max_uses' => 'nullable|integer|min:1',
            'max_uses_per_user' => 'required|integer|min:1',
            'expires_at' => 'nullable|date',
            'is_active' => 'sometimes',
        ]);
        $validated['code'] = strtoupper($validated['code']);
        $validated['is_active'] = $request->has('is_active');
        PortfolioCoupon::create($validated);
        return back()->with('success', 'Coupon created.');
    }

    public function toggleCoupon(int $id)
    {
        $coupon = PortfolioCoupon::findOrFail($id);
        $coupon->update(['is_active' => !$coupon->is_active]);
        return back()->with('success', $coupon->is_active ? 'Coupon activated.' : 'Coupon deactivated.');
    }

    // --- Orders ---
    public function orders(Request $request)
    {
        $query = PortfolioOrder::with(['user:id,name,email', 'plan:id,name,slug', 'coupon:id,code']);
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }
        $orders = $query->latest()->paginate(20)->withQueryString();
        return view('admin.orders', compact('orders'));
    }

    // --- Subscriptions ---
    public function subscriptions(Request $request)
    {
        $query = PortfolioSubscription::with(['user:id,name,email', 'plan:id,name,slug']);
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }
        $subscriptions = $query->latest()->paginate(20)->withQueryString();
        return view('admin.subscriptions', compact('subscriptions'));
    }

    public function refundSubscription(int $id, RazorpayService $razorpay)
    {
        $subscription = PortfolioSubscription::with('order')->findOrFail($id);

        if ($subscription->status->value === 'refunded') {
            return back()->with('error', 'Already refunded.');
        }

        $order = $subscription->order;
        if ($order && $order->razorpay_payment_id && $order->final_amount > 0) {
            try {
                $razorpay->refund($order->razorpay_payment_id);
            } catch (\Exception $e) {
                Log::error('Razorpay refund failed', ['error' => $e->getMessage()]);
                return back()->with('error', 'Refund failed: ' . $e->getMessage());
            }
            $order->update(['status' => 'refunded']);
        }

        $subscription->update(['status' => 'refunded']);
        Portfolio::where('user_id', $subscription->user_id)->update(['is_published' => false]);

        return back()->with('success', 'Subscription refunded and portfolio unpublished.');
    }

    // --- Templates ---
    public function templates()
    {
        $templates = PortfolioTemplate::orderBy('sort_order')->get();
        return view('admin.templates', compact('templates'));
    }

    public function toggleTemplate(int $id)
    {
        $template = PortfolioTemplate::findOrFail($id);
        $template->update(['is_active' => !$template->is_active]);
        return back()->with('success', $template->is_active ? 'Template activated.' : 'Template deactivated.');
    }
}
