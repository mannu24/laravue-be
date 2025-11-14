<?php

namespace App\Http\Controllers\v1\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\HttpResponse;
use App\Models\PushSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

class PushSubscriptionController extends Controller
{
    use HttpResponse;

    /**
     * Subscribe to push notifications
     */
    public function subscribe(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'endpoint' => 'required|url|max:500',
                'keys' => 'required|array',
                'keys.p256dh' => 'required|string',
                'keys.auth' => 'required|string',
                'content_encoding' => 'nullable|string|in:aesgcm,aes128gcm',
            ]);

            if ($validator->fails()) {
                return $this->error(
                    data: $validator->errors(),
                    message: 'Validation failed',
                    code: 422
                );
            }

            $userId = auth()->guard('api')->id();

            // Check if subscription already exists
            $subscription = PushSubscription::where('endpoint', $request->input('endpoint'))
                ->where('user_id', $userId)
                ->first();

            if ($subscription) {
                // Update existing subscription
                $subscription->update([
                    'public_key' => $request->input('keys.p256dh'),
                    'auth_token' => $request->input('keys.auth'),
                    'content_encoding' => $request->input('content_encoding', 'aesgcm'),
                ]);
            } else {
                // Create new subscription
                $subscription = PushSubscription::create([
                    'user_id' => $userId,
                    'endpoint' => $request->input('endpoint'),
                    'public_key' => $request->input('keys.p256dh'),
                    'auth_token' => $request->input('keys.auth'),
                    'content_encoding' => $request->input('content_encoding', 'aesgcm'),
                ]);
            }

            return $this->success(
                data: [
                    'id' => $subscription->id,
                    'endpoint' => $subscription->endpoint,
                ],
                message: 'Push subscription saved successfully'
            );
        } catch (Exception $e) {
            return $this->internalError(
                message: 'Failed to save push subscription'
            );
        }
    }

    /**
     * Unsubscribe from push notifications
     */
    public function unsubscribe(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'endpoint' => 'required|url|max:500',
            ]);

            if ($validator->fails()) {
                return $this->error(
                    data: $validator->errors(),
                    message: 'Validation failed',
                    code: 422
                );
            }

            $userId = auth()->guard('api')->id();

            $subscription = PushSubscription::where('endpoint', $request->input('endpoint'))
                ->where('user_id', $userId)
                ->first();

            if ($subscription) {
                $subscription->delete();
            }

            return $this->success(
                message: 'Push subscription removed successfully'
            );
        } catch (Exception $e) {
            return $this->internalError(
                message: 'Failed to remove push subscription'
            );
        }
    }

    /**
     * Get user's push subscriptions
     */
    public function index(): JsonResponse
    {
        try {
            $userId = auth()->guard('api')->id();
            $subscriptions = PushSubscription::where('user_id', $userId)
                ->select('id', 'endpoint', 'created_at')
                ->get();

            return $this->success(
                data: [
                    'subscriptions' => $subscriptions,
                    'count' => $subscriptions->count(),
                ],
                message: 'Push subscriptions retrieved successfully'
            );
        } catch (Exception $e) {
            return $this->internalError(
                message: 'Failed to retrieve push subscriptions'
            );
        }
    }

    /**
     * Get VAPID public key
     */
    public function getVapidKey(): JsonResponse
    {
        try {
            $publicKey = config('services.webpush.vapid_public_key');
            
            if (!$publicKey) {
                return $this->error(
                    message: 'VAPID keys not configured',
                    code: 503
                );
            }

            return $this->success(
                data: [
                    'public_key' => $publicKey,
                ],
                message: 'VAPID public key retrieved successfully'
            );
        } catch (Exception $e) {
            return $this->internalError(
                message: 'Failed to retrieve VAPID key'
            );
        }
    }
}
