<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\HttpResponse;
use App\Models\PortfolioCoupon;
use App\Models\PortfolioCouponUse;
use App\Models\PortfolioOrder;
use App\Models\PortfolioPlan;
use App\Models\PortfolioSubscription;
use App\Services\RazorpayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PortfolioPaymentController extends Controller
{
    use HttpResponse;

    public function __construct(
        protected RazorpayService $razorpay
    ) {
    }

    /**
     * Create a Razorpay order for a plan purchase.
     */
    public function createOrder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'plan_slug' => 'required|string|exists:portfolio_plans,slug',
            'coupon_code' => 'nullable|string|max:20',
        ]);

        $user = $request->user();
        $plan = PortfolioPlan::where('slug', $validated['plan_slug'])->where('is_active', true)->firstOrFail();

        $amount = (float) $plan->price;
        $discountAmount = 0;
        $coupon = null;

        // Apply coupon if provided
        if (!empty($validated['coupon_code'])) {
            $coupon = PortfolioCoupon::where('code', strtoupper($validated['coupon_code']))->first();

            if (!$coupon || !$coupon->isValidForUser($user->id)) {
                return $this->error(null, 'Invalid or expired coupon code.', 422);
            }

            if (!$coupon->appliesToPlan($plan->slug)) {
                return $this->error(null, 'This coupon does not apply to the selected plan.', 422);
            }

            $discountAmount = $coupon->calculateDiscount($amount);
        }

        $finalAmount = max(0, round($amount - $discountAmount, 2));

        // Create the order record
        $order = PortfolioOrder::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'coupon_id' => $coupon?->id,
            'amount' => $amount,
            'discount_amount' => $discountAmount,
            'final_amount' => $finalAmount,
            'status' => 'pending',
        ]);

        // If 100% discount — activate immediately without Razorpay
        if ($finalAmount <= 0) {
            return $this->activateFreeOrder($order, $plan, $coupon, $user);
        }

        // Create Razorpay order
        try {
            $razorpayOrder = $this->razorpay->createOrder(
                amountInPaise: (int) ($finalAmount * 100),
                receipt: 'portfolio_order_' . $order->id,
                notes: [
                    'user_id' => $user->id,
                    'plan' => $plan->slug,
                    'order_id' => $order->id,
                ]
            );

            $order->update(['razorpay_order_id' => $razorpayOrder['id']]);

            return $this->success([
                'order_id' => $order->id,
                'razorpay_order_id' => $razorpayOrder['id'],
                'razorpay_key_id' => $this->razorpay->getKeyId(),
                'amount' => $finalAmount,
                'amount_in_paise' => (int) ($finalAmount * 100),
                'currency' => 'INR',
                'plan' => $plan->only(['name', 'slug', 'duration_months']),
                'discount_amount' => $discountAmount,
            ]);
        } catch (\Exception $e) {
            $order->update(['status' => 'failed']);
            Log::error('Razorpay order creation failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
            return $this->error(null, 'Payment gateway error. Please try again.', 500);
        }
    }

    /**
     * Verify payment after Razorpay checkout.
     */
    public function verifyPayment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => 'required|integer|exists:portfolio_orders,id',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature' => 'required|string',
        ]);

        $order = PortfolioOrder::where('id', $validated['order_id'])
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        // Idempotency — already paid
        if ($order->status->value === 'paid') {
            return $this->success(['already_paid' => true], 'Payment already verified.');
        }

        if (!$order->razorpay_order_id) {
            return $this->error(null, 'Invalid order.', 422);
        }

        // Verify signature
        $isValid = $this->razorpay->verifySignature(
            $order->razorpay_order_id,
            $validated['razorpay_payment_id'],
            $validated['razorpay_signature']
        );

        if (!$isValid) {
            $order->update(['status' => 'failed']);
            return $this->error(null, 'Payment verification failed.', 422);
        }

        // Activate subscription
        return DB::transaction(function () use ($order, $validated, $request) {
            $order->update([
                'razorpay_payment_id' => $validated['razorpay_payment_id'],
                'razorpay_signature' => $validated['razorpay_signature'],
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            $subscription = $this->createSubscription($order);

            // Record coupon use
            if ($order->coupon_id) {
                $this->recordCouponUse($order);
            }

            return $this->success([
                'subscription' => $subscription->load('plan'),
                'message' => 'Payment successful! Your portfolio subscription is now active.',
            ]);
        });
    }

    /**
     * Validate a coupon code (without creating an order).
     */
    public function validateCoupon(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20',
            'plan_slug' => 'required|string|exists:portfolio_plans,slug',
        ]);

        $coupon = PortfolioCoupon::where('code', strtoupper($validated['code']))->first();

        if (!$coupon) {
            return $this->error(null, 'Coupon not found.', 404);
        }

        if (!$coupon->isValidForUser($request->user()->id)) {
            return $this->error(null, 'This coupon is no longer valid or has been used.', 422);
        }

        if (!$coupon->appliesToPlan($validated['plan_slug'])) {
            return $this->error(null, 'This coupon does not apply to the selected plan.', 422);
        }

        $plan = PortfolioPlan::where('slug', $validated['plan_slug'])->firstOrFail();
        $discount = $coupon->calculateDiscount((float) $plan->price);

        return $this->success([
            'valid' => true,
            'discount_type' => $coupon->discount_type,
            'discount_value' => $coupon->discount_value,
            'discount_amount' => $discount,
            'final_amount' => max(0, round((float) $plan->price - $discount, 2)),
        ]);
    }

    /**
     * Get current subscription status.
     */
    public function subscription(Request $request): JsonResponse
    {
        $subscription = $request->user()->activePortfolioSubscription;

        if (!$subscription) {
            // Check for any subscription (even expired)
            $latest = PortfolioSubscription::where('user_id', $request->user()->id)
                ->latest('created_at')
                ->first();

            return $this->success([
                'active' => false,
                'latest_subscription' => $latest?->load('plan'),
            ]);
        }

        return $this->success([
            'active' => true,
            'subscription' => $subscription->load('plan'),
            'is_grace_period' => $subscription->isInGracePeriod(),
            'days_remaining' => (int) now()->diffInDays($subscription->expires_at, false),
        ]);
    }

    /**
     * Handle 100% discount (free) orders.
     */
    protected function activateFreeOrder(PortfolioOrder $order, PortfolioPlan $plan, ?PortfolioCoupon $coupon, $user): JsonResponse
    {
        return DB::transaction(function () use ($order, $plan, $coupon, $user) {
            $order->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            $subscription = $this->createSubscription($order);

            if ($coupon) {
                $this->recordCouponUse($order);
            }

            return $this->success([
                'free_order' => true,
                'subscription' => $subscription->load('plan'),
                'message' => 'Coupon applied! Your portfolio subscription is now active.',
            ]);
        });
    }

    /**
     * Create a subscription from a paid order.
     */
    protected function createSubscription(PortfolioOrder $order): PortfolioSubscription
    {
        $plan = $order->plan;
        $user = $order->user;

        // Check if user has an existing active subscription — extend it
        $existing = PortfolioSubscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->first();

        $startsAt = $existing ? $existing->expires_at : now();
        $expiresAt = $startsAt->copy()->addMonths($plan->duration_months);
        $graceEndsAt = $expiresAt->copy()->addDays(config('portfolio.grace_period_days', 7));

        // If extending, update the existing subscription
        if ($existing) {
            $existing->update([
                'plan_id' => $plan->id,
                'order_id' => $order->id,
                'expires_at' => $expiresAt,
                'grace_ends_at' => $graceEndsAt,
            ]);
            return $existing->fresh();
        }

        return PortfolioSubscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'order_id' => $order->id,
            'starts_at' => $startsAt,
            'expires_at' => $expiresAt,
            'grace_ends_at' => $graceEndsAt,
            'status' => 'active',
        ]);
    }

    /**
     * Record coupon usage after successful payment.
     */
    protected function recordCouponUse(PortfolioOrder $order): void
    {
        PortfolioCouponUse::create([
            'coupon_id' => $order->coupon_id,
            'user_id' => $order->user_id,
            'order_id' => $order->id,
        ]);

        PortfolioCoupon::where('id', $order->coupon_id)->increment('used_count');
    }
}
