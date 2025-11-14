<?php

namespace App\Http\Controllers\v1\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Get user notifications
     */
    public function index(Request $request)
    {
        $perPage = min((int)$request->input('per_page', 20), 50);
        $unreadOnly = $request->boolean('unread_only', false);

        $query = Notification::with(['notifiable:id,name,username'])
            ->where('user_id', Auth::guard('api')->id())
            ->orderBy('created_at', 'desc');

        if ($unreadOnly) {
            $query->where('read', false);
        }

        $notifications = $query->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => [
                'notifications' => $notifications->items(),
                'pagination' => [
                    'current_page' => $notifications->currentPage(),
                    'per_page' => $notifications->perPage(),
                    'total' => $notifications->total(),
                    'last_page' => $notifications->lastPage(),
                    'unread_count' => Notification::where('user_id', Auth::guard('api')->id())
                        ->where('read', false)
                        ->count(),
                ],
            ],
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        $notification = Notification::where('user_id', Auth::guard('api')->id())
            ->findOrFail($id);

        $notification->markAsRead();

        return response()->json([
            'status' => 'success',
            'message' => 'Notification marked as read',
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        Notification::where('user_id', Auth::guard('api')->id())
            ->where('read', false)
            ->update([
                'read' => true,
                'read_at' => now(),
            ]);

        return response()->json([
            'status' => 'success',
            'message' => 'All notifications marked as read',
        ]);
    }

    /**
     * Get unread count
     */
    public function unreadCount()
    {
        $count = Notification::where('user_id', Auth::guard('api')->id())
            ->where('read', false)
            ->count();

        return response()->json([
            'status' => 'success',
            'data' => [
                'unread_count' => $count,
            ],
        ]);
    }

    /**
     * Delete notification
     */
    public function destroy($id)
    {
        $notification = Notification::where('user_id', Auth::guard('api')->id())
            ->findOrFail($id);

        $notification->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Notification deleted',
        ]);
    }

    /**
     * Delete multiple notifications
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|integer|exists:notifications,id',
        ]);

        $userId = Auth::guard('api')->id();
        $ids = $request->input('ids');

        // Ensure all notifications belong to the authenticated user
        $deletedCount = Notification::where('user_id', $userId)
            ->whereIn('id', $ids)
            ->delete();

        return response()->json([
            'status' => 'success',
            'message' => "{$deletedCount} notification(s) deleted successfully",
            'data' => [
                'deleted_count' => $deletedCount,
            ],
        ]);
    }
}
