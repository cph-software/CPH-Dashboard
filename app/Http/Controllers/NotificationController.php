<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Get unread notifications for the authenticated user.
     */
    public function getUnread(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $unreadCount = $user->unreadNotifications()->count();
        $notifications = $user->unreadNotifications()->take(10)->get()->map(function ($notif) {
            $data = $notif->data;
            return [
                'id'         => $notif->id,
                'message'    => $data['message'] ?? 'Notification',
                'module'     => $data['module'] ?? '',
                'user_name'  => $data['user_name'] ?? 'System',
                'created_at' => $notif->created_at->diffForHumans(),
                'time_raw'   => $notif->created_at->toIso8601String(),
                'details'    => $data['details'] ?? []
            ];
        });

        return response()->json([
            'success' => true,
            'count'   => $unreadCount,
            'data'    => $notifications
        ]);
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['success' => false], 401);
        }

        $notification = $user->unreadNotifications()->where('id', $id)->first();
        if ($notification) {
            $notification->markAsRead();
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Not found'], 404);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request)
    {
        $user = auth()->user();
        if ($user) {
            $user->unreadNotifications->markAsRead();
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false], 401);
    }
}
