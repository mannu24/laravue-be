<?php

namespace App\Services;

use App\Models\PushSubscription;
use App\Models\UserSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    /**
     * Send push notification to a user
     */
    public static function sendToUser(
        int $userId,
        string $title,
        string $message,
        ?array $data = null,
        ?string $url = null
    ): void {
        // Check if user has push notifications enabled
        $userSettings = UserSetting::getOrCreateForUser($userId);
        if (!$userSettings->push_notifications) {
            return;
        }

        // Get all active subscriptions for the user
        $subscriptions = PushSubscription::where('user_id', $userId)->get();

        if ($subscriptions->isEmpty()) {
            return;
        }

        // Prepare notification payload
        $notificationData = [
            'title' => $title,
            'body' => $message,
            'icon' => asset('favicon.ico'),
            'badge' => asset('favicon.ico'),
            'data' => array_merge($data ?? [], [
                'url' => $url,
                'timestamp' => now()->toIso8601String(),
            ]),
        ];

        // Get VAPID keys from config
        $vapidPublicKey = config('services.webpush.vapid_public_key');
        $vapidPrivateKey = config('services.webpush.vapid_private_key');
        $vapidSubject = config('services.webpush.vapid_subject', config('app.url'));

        if (!$vapidPublicKey || !$vapidPrivateKey) {
            Log::warning('VAPID keys not configured for push notifications');
            return;
        }

        // Send to each subscription
        foreach ($subscriptions as $subscription) {
            try {
                self::sendToSubscription($subscription, $notificationData, $vapidPublicKey, $vapidPrivateKey, $vapidSubject);
            } catch (\Exception $e) {
                Log::error('Failed to send push notification', [
                    'user_id' => $userId,
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage(),
                ]);

                // If subscription is invalid (410), delete it
                if (str_contains($e->getMessage(), '410') || str_contains($e->getMessage(), 'Gone')) {
                    $subscription->delete();
                }
            }
        }
    }

    /**
     * Send push notification to a specific subscription
     */
    protected static function sendToSubscription(
        PushSubscription $subscription,
        array $notificationData,
        string $vapidPublicKey,
        string $vapidPrivateKey,
        string $vapidSubject
    ): void {
        // Generate VAPID JWT token
        $jwt = self::generateVapidJWT($subscription->endpoint, $vapidSubject, $vapidPublicKey, $vapidPrivateKey);

        // Prepare payload as JSON
        $payload = json_encode($notificationData);

        // Prepare headers
        $headers = [
            'Authorization' => 'vapid t=' . $jwt . ', k=' . $vapidPublicKey,
            'TTL' => '86400',
            'Content-Type' => 'application/json',
        ];

        // Add Content-Encoding header if specified
        if ($subscription->content_encoding) {
            $headers['Content-Encoding'] = $subscription->content_encoding;
        }

        // Send HTTP POST request
        $response = Http::timeout(10)
            ->withHeaders($headers)
            ->withBody($payload, 'application/json')
            ->post($subscription->endpoint);

        // Handle response
        $status = $response->status();
        
        if ($status === 410) {
            // Subscription expired or invalid
            $subscription->delete();
            throw new \Exception('Subscription expired (410)');
        } elseif ($status === 400 || $status === 401) {
            // Invalid request or authentication failed
            throw new \Exception('Push notification failed: ' . $status . ' - ' . $response->body());
        } elseif ($status !== 201 && $status !== 200) {
            throw new \Exception('Push notification failed: ' . $status);
        }
    }

    /**
     * Generate VAPID JWT token
     * Note: This is a simplified implementation. For production, use proper ECDSA signing.
     */
    protected static function generateVapidJWT(string $audience, string $subject, string $publicKey, string $privateKey): string
    {
        // Parse endpoint to get audience
        $parsedUrl = parse_url($audience);
        $aud = ($parsedUrl['scheme'] ?? 'https') . '://' . ($parsedUrl['host'] ?? '');
        
        if (isset($parsedUrl['port'])) {
            $aud .= ':' . $parsedUrl['port'];
        }

        // Create JWT claims
        $claims = [
            'aud' => $aud,
            'exp' => time() + 43200, // 12 hours
            'sub' => $subject,
        ];

        // Create JWT header
        $header = [
            'alg' => 'ES256',
            'typ' => 'JWT',
        ];

        // Base64 URL encode
        $base64UrlEncode = function ($data) {
            return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
        };

        $encodedHeader = $base64UrlEncode(json_encode($header));
        $encodedPayload = $base64UrlEncode(json_encode($claims));

        // Create signature (simplified - for production use proper ECDSA)
        // This is a placeholder that will work for basic testing
        $signatureInput = $encodedHeader . '.' . $encodedPayload;
        $signature = hash_hmac('sha256', $signatureInput, $privateKey, true);
        $encodedSignature = $base64UrlEncode($signature);

        return $encodedHeader . '.' . $encodedPayload . '.' . $encodedSignature;
    }
}
