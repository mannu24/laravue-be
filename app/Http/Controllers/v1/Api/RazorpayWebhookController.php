<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1\Api;

use App\Http\Controllers\Controller;
use App\Models\PortfolioCoupon;
use App\Models\PortfolioCouponUse;
use App\Models\PortfolioOrder;
use App\Models\PortfolioSubscription;
use App\Services\RazorpayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RazorpayWebhookController extends Controller
{
    public function __construct(
        protected RazorpayService $razorpay
    ) {
    }

    /**
     * Handle Razorpay webhook events.
     */
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $signature = $request->header('X-Razorpay-Signature', '');

        // Verify webhook signature
        if (!$this->razorpay->verifyWebhookSignature($payload, $signature)) {
            Log::warning('Razorpay webhook: invalid signature');
            return response()->json(['status' => 'invalid_signature'], 400);
        }

        $data = json_decode($payload, true);
        $event = $data['event'] ?? '';

        Log::info('Razorpay webhook received', ['event' => $event]);

        match ($event) {
            'payment.captured', 'order.paid' => $this->handlePaymentCaptured($data),
            'payment.failed' => $this->handlePaymentFailed($data),
            default => Log::info('Razorpay webhook: unhandled event', ['event' => $event]),
        };

        return response()->json(['status' => 'ok']);
    }

    /**
     * Handle successful payment.
     */
    protected function handlePaymentCaptured(array $data): void
    {
        $payment = $data['payload']['payment']['entity'] ?? [];
        $razorpayOrderId = $payment['order_id'] ?? null;

        if (!$razorpayOrderId) return;

        $order = PortfolioOrder::where('razorpay_order_id', $razorpayOrderId)->first();
        if (!$order) return;

        // Idempotency — already processed
        if ($order->status->value === 'paid') return;

        DB::transaction(function () use ($order, $payment) {
            $order->update([
                'razorpay_payment_id' => $payment['id'] ?? $order->razorpay_payment_id,
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            // Create subscription if not already created
            $existingSub = PortfolioSubscription::where('order_id', $order->id)->first();
            if (!$existingSub) {
                $plan = $order->plan;
                $startsAt = now();
                $expiresAt = $startsAt->copy()->addMonths($plan->duration_months);
                $graceEndsAt = $expiresAt->copy()->addDays(config('portfolio.grace_period_days', 7));

                PortfolioSubscription::create([
                    'user_id' => $order->user_id,
                    'plan_id' => $plan->id,
                    'order_id' => $order->id,
                    'starts_at' => $startsAt,
                    'expires_at' => $expiresAt,
                    'grace_ends_at' => $graceEndsAt,
                    'status' => 'active',
                ]);
            }

            // Record coupon use if not already recorded
            if ($order->coupon_id) {
                $alreadyRecorded = PortfolioCouponUse::where('order_id', $order->id)->exists();
                if (!$alreadyRecorded) {
                    PortfolioCouponUse::create([
                        'coupon_id' => $order->coupon_id,
                        'user_id' => $order->user_id,
                        'order_id' => $order->id,
                    ]);
                    PortfolioCoupon::where('id', $order->coupon_id)->increment('used_count');
                }
            }
        });

        Log::info('Razorpay webhook: payment captured', ['order_id' => $order->id]);
    }

    /**
     * Handle failed payment.
     */
    protected function handlePaymentFailed(array $data): void
    {
        $payment = $data['payload']['payment']['entity'] ?? [];
        $razorpayOrderId = $payment['order_id'] ?? null;

        if (!$razorpayOrderId) return;

        $order = PortfolioOrder::where('razorpay_order_id', $razorpayOrderId)->first();
        if (!$order || $order->status->value === 'paid') return;

        $order->update(['status' => 'failed']);

        Log::info('Razorpay webhook: payment failed', ['order_id' => $order->id]);
    }
}
