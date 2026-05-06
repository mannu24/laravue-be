<?php

declare(strict_types=1);

namespace App\Services;

use Razorpay\Api\Api;
use Illuminate\Support\Facades\Log;

class RazorpayService
{
    protected ?Api $api = null;

    protected function getApi(): Api
    {
        if (!$this->api) {
            $keyId = config('services.razorpay.key_id');
            $keySecret = config('services.razorpay.key_secret');

            if (!$keyId || !$keySecret) {
                throw new \RuntimeException('Razorpay credentials not configured.');
            }

            $this->api = new Api($keyId, $keySecret);
        }

        return $this->api;
    }

    /**
     * Create a Razorpay order.
     *
     * @param int $amountInPaise Amount in paise (₹299 = 29900)
     * @param string $receipt Unique receipt ID
     * @param array $notes Optional notes
     * @return array Order data from Razorpay
     */
    public function createOrder(int $amountInPaise, string $receipt, array $notes = []): array
    {
        $order = $this->getApi()->order->create([
            'amount' => $amountInPaise,
            'currency' => 'INR',
            'receipt' => $receipt,
            'notes' => $notes,
        ]);

        return $order->toArray();
    }

    /**
     * Verify payment signature.
     *
     * @param string $orderId Razorpay order ID
     * @param string $paymentId Razorpay payment ID
     * @param string $signature Razorpay signature
     * @return bool
     */
    public function verifySignature(string $orderId, string $paymentId, string $signature): bool
    {
        try {
            $this->getApi()->utility->verifyPaymentSignature([
                'razorpay_order_id' => $orderId,
                'razorpay_payment_id' => $paymentId,
                'razorpay_signature' => $signature,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::warning('Razorpay signature verification failed', [
                'order_id' => $orderId,
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Verify webhook signature.
     *
     * @param string $payload Raw request body
     * @param string $signature X-Razorpay-Signature header
     * @return bool
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        try {
            $webhookSecret = config('services.razorpay.webhook_secret');
            if (!$webhookSecret) return false;

            $this->getApi()->utility->verifyWebhookSignature($payload, $signature, $webhookSecret);
            return true;
        } catch (\Exception $e) {
            Log::warning('Razorpay webhook signature verification failed', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Initiate a refund.
     *
     * @param string $paymentId Razorpay payment ID
     * @param int|null $amountInPaise Amount to refund (null = full refund)
     * @return array Refund data
     */
    public function refund(string $paymentId, ?int $amountInPaise = null): array
    {
        $params = [];
        if ($amountInPaise) {
            $params['amount'] = $amountInPaise;
        }

        $refund = $this->getApi()->payment->fetch($paymentId)->refund($params);
        return $refund->toArray();
    }

    /**
     * Get the Razorpay key ID (for frontend).
     */
    public function getKeyId(): string
    {
        return config('services.razorpay.key_id', '');
    }
}
