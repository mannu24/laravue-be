<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\HttpResponse;
use App\Models\PortfolioOrder;
use App\Models\PortfolioSubscription;
use App\Services\RazorpayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminOrderController extends Controller
{
    use HttpResponse;

    public function index(Request $request): JsonResponse
    {
        $query = PortfolioOrder::with(['user:id,name,email', 'plan:id,name,slug', 'coupon:id,code']);

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $orders = $query->latest()->paginate(20);

        return $this->success($orders);
    }

    public function subscriptions(Request $request): JsonResponse
    {
        $query = PortfolioSubscription::with(['user:id,name,email', 'plan:id,name,slug']);

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $subscriptions = $query->latest()->paginate(20);

        return $this->success($subscriptions);
    }

    public function refund(Request $request, int $id, RazorpayService $razorpay): JsonResponse
    {
        $subscription = PortfolioSubscription::with('order')->findOrFail($id);

        if ($subscription->status->value === 'refunded') {
            return $this->error(null, 'Already refunded.', 422);
        }

        $order = $subscription->order;

        // Process Razorpay refund if payment was made
        if ($order && $order->razorpay_payment_id && $order->final_amount > 0) {
            try {
                $razorpay->refund($order->razorpay_payment_id);
            } catch (\Exception $e) {
                Log::error('Razorpay refund failed', [
                    'subscription_id' => $subscription->id,
                    'payment_id' => $order->razorpay_payment_id,
                    'error' => $e->getMessage(),
                ]);
                return $this->error(null, 'Refund failed: ' . $e->getMessage(), 500);
            }

            $order->update(['status' => 'refunded']);
        }

        $subscription->update(['status' => 'refunded']);

        // Unpublish portfolio
        $portfolio = \App\Models\Portfolio::where('user_id', $subscription->user_id)->first();
        if ($portfolio) {
            $portfolio->update(['is_published' => false]);
        }

        return $this->success(null, 'Subscription refunded and portfolio unpublished.');
    }
}
