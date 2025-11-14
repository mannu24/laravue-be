<?php

namespace App\Services;

use App\Events\NotificationCreated;
use App\Jobs\SendNotificationEmail;
use App\Models\Notification;
use App\Models\User;
use App\Models\UserSetting;
use App\Services\PushNotificationService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Create a notification and optionally send email
     */
    public static function create(
        int $userId,
        string $type,
        string $title,
        string $message,
        ?Model $subject = null,
        ?int $notifiableId = null,
        ?array $data = null,
        bool $sendEmail = true,
        ?string $emailBlade = null,
        ?string $emailSubject = null
    ): ?Notification {
        // Don't notify yourself
        if ($notifiableId && $userId === $notifiableId) {
            return null;
        }

        $notification = Notification::create([
            'user_id' => $userId,
            'notifiable_id' => $notifiableId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject ? $subject->id : null,
            'data' => $data,
            'read' => false,
            'email_sent' => false,
        ]);

        // Eager load notifiable relationship for broadcasting
        if ($notifiableId) {
            $notification->load('notifiable:id,name,username');
        }

        // Get user settings once for both email and push notifications
        $userSettings = UserSetting::getOrCreateForUser($userId);
        $preferences = $userSettings->notification_preferences ?? UserSetting::getDefaultPreferences();

        // Queue email notification if enabled
        if ($sendEmail && $emailBlade && $emailSubject) {
            $user = User::find($userId);
            if ($user && $user->email) {
                // Check user settings for email notifications
                if (!$userSettings->email_notifications) {
                    // Don't send email if disabled, but continue to check push notifications
                } elseif (!isset($preferences[$type]) || $preferences[$type]) {
                    // Check granular notification preferences
                    // Only send email if this type is enabled (default is true)
                    SendNotificationEmail::dispatch(
                        $user->email,
                        $user->name,
                        $emailBlade,
                        $emailSubject,
                        [
                            'title' => $title,
                            'notificationMessage' => $message,
                            'type' => $type,
                            'notifiable' => $notifiableId ? User::find($notifiableId) : null,
                            'subject' => $subject,
                            'data' => $data,
                        ]
                    );
                }
            }
        }

        // Send push notification if enabled
        if ($userSettings->push_notifications) {
            // Check granular notification preferences
            if (!isset($preferences[$type]) || $preferences[$type]) {
                try {
                    PushNotificationService::sendToUser(
                        userId: $userId,
                        title: $title,
                        message: $message,
                        data: $data,
                        url: $data['url'] ?? null
                    );
                } catch (\Exception $e) {
                    // Log error but don't fail the notification creation
                    Log::error('Failed to send push notification', [
                        'user_id' => $userId,
                        'type' => $type,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        // Broadcast real-time notification event
        try {
            broadcast(new NotificationCreated($notification))->toOthers();
        } catch (\Exception $e) {
            // Log error but don't fail the notification creation
            Log::error('Failed to broadcast notification', [
                'notification_id' => $notification->id,
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
        }

        return $notification;
    }

    /**
     * Notify multiple users
     */
    public static function notifyMany(
        array $userIds,
        string $type,
        string $title,
        string $message,
        ?Model $subject = null,
        ?int $notifiableId = null,
        ?array $data = null,
        bool $sendEmail = true,
        ?string $emailBlade = null,
        ?string $emailSubject = null
    ): void {
        foreach ($userIds as $userId) {
            self::create(
                $userId,
                $type,
                $title,
                $message,
                $subject,
                $notifiableId,
                $data,
                $sendEmail,
                $emailBlade,
                $emailSubject
            );
        }
    }

    /**
     * Mark notification as email sent
     */
    public static function markEmailSent(int $notificationId): void
    {
        Notification::where('id', $notificationId)->update(['email_sent' => true]);
    }

    /**
     * Delete a recent follow notification if it was created within the cooldown period
     * Also stores a cache entry to track the deletion for follow->unfollow->follow cycles
     * 
     * @param int $userId The user who received the notification (user being followed)
     * @param int $notifiableId The user who triggered the notification (user who followed)
     * @param int $cooldownMinutes Cooldown period in minutes (default: 2)
     * @return bool True if notification was deleted, false otherwise
     */
    public static function deleteRecentFollowNotification(
        int $userId,
        int $notifiableId,
        int $cooldownMinutes = 2
    ): bool {
        $cooldownTime = now()->subMinutes($cooldownMinutes);

        $notification = Notification::where('user_id', $userId)
            ->where('notifiable_id', $notifiableId)
            ->where('type', Notification::TYPE_FOLLOWED)
            ->where('created_at', '>=', $cooldownTime)
            ->first();

        if ($notification) {
            $notification->delete();
            
            // Store deletion timestamp in cache to prevent re-notification
            // if user follows again within cooldown period
            $cacheKey = "deleted_follow_notification_{$userId}_{$notifiableId}";
            Cache::put($cacheKey, now(), now()->addMinutes($cooldownMinutes + 1));
            
            return true;
        }

        return false;
    }

    /**
     * Check if a follow notification was recently created (within cooldown period)
     * Also checks for recently deleted notifications to handle follow->unfollow->follow cycles
     * 
     * @param int $userId The user who received the notification (user being followed)
     * @param int $notifiableId The user who triggered the notification (user who followed)
     * @param int $cooldownMinutes Cooldown period in minutes (default: 2)
     * @return bool True if notification exists within cooldown period or was recently deleted
     */
    public static function hasRecentFollowNotification(
        int $userId,
        int $notifiableId,
        int $cooldownMinutes = 2
    ): bool {
        $cooldownTime = now()->subMinutes($cooldownMinutes);

        // Check if there's an existing notification within cooldown period
        $hasActiveNotification = Notification::where('user_id', $userId)
            ->where('notifiable_id', $notifiableId)
            ->where('type', Notification::TYPE_FOLLOWED)
            ->where('created_at', '>=', $cooldownTime)
            ->exists();

        if ($hasActiveNotification) {
            return true;
        }

        // Check if there was a notification deleted within cooldown period
        // We use a cache key to track deleted notifications
        $cacheKey = "deleted_follow_notification_{$userId}_{$notifiableId}";
        $deletedAt = Cache::get($cacheKey);

        if ($deletedAt && now()->diffInMinutes($deletedAt) < $cooldownMinutes) {
            return true;
        }

        return false;
    }
}

