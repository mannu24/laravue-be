<?php

namespace App\Http\Controllers\v1\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\HttpResponse;
use App\Models\UserSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

class SettingsController extends Controller
{
    use HttpResponse;

    /**
     * Get notification settings
     */
    public function getNotifications(): JsonResponse
    {
        try {
            $userId = auth()->guard('api')->id();
            $settings = UserSetting::getOrCreateForUser($userId);

            return $this->success(
                data: [
                    'email_notifications' => $settings->email_notifications,
                    'push_notifications' => $settings->push_notifications,
                    'notification_preferences' => $settings->notification_preferences ?? UserSetting::getDefaultPreferences(),
                ],
                message: 'Notification settings retrieved successfully'
            );
        } catch (Exception $e) {
            return $this->internalError(
                message: 'Failed to retrieve notification settings'
            );
        }
    }

    /**
     * Update notification settings
     */
    public function updateNotifications(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'email_notifications' => 'sometimes|boolean',
                'push_notifications' => 'sometimes|boolean',
                'notification_preferences' => 'sometimes|array',
                'notification_preferences.*' => 'boolean',
            ]);

            if ($validator->fails()) {
                return $this->error(
                    data: $validator->errors(),
                    message: 'Validation failed',
                    code: 422
                );
            }

            $userId = auth()->guard('api')->id();
            $settings = UserSetting::getOrCreateForUser($userId);

            $updateData = [];
            
            if ($request->has('email_notifications')) {
                $updateData['email_notifications'] = $request->boolean('email_notifications');
            }

            if ($request->has('push_notifications')) {
                $updateData['push_notifications'] = $request->boolean('push_notifications');
            }

            if ($request->has('notification_preferences')) {
                $currentPreferences = $settings->notification_preferences ?? UserSetting::getDefaultPreferences();
                $newPreferences = array_merge($currentPreferences, $request->input('notification_preferences'));
                $updateData['notification_preferences'] = $newPreferences;
            }

            $settings->update($updateData);
            $settings->refresh();

            return $this->success(
                data: [
                    'email_notifications' => $settings->email_notifications,
                    'push_notifications' => $settings->push_notifications,
                    'notification_preferences' => $settings->notification_preferences ?? UserSetting::getDefaultPreferences(),
                ],
                message: 'Notification settings updated successfully'
            );
        } catch (Exception $e) {
            return $this->internalError(
                message: 'Failed to update notification settings'
            );
        }
    }
}
