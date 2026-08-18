<?php

namespace App\Http\Controllers\V1\Api\Common;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Get list of notifications for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['notifications' => []]);
        }

        $notifications = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'notifications' => $notifications,
        ]);
    }

    /**
     * Mark a specific notification as read.
     */
    public function read(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $notification = Notification::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (! $notification) {
            return response()->json(['error' => 'Notification not found'], 404);
        }

        $notification->update([
            'is_read' => true,
        ]);

        return response()->json([
            'success' => true,
            'notification' => $notification,
        ]);
    }

    /**
     * Mark all notifications as read for the authenticated user.
     */
    public function readAll(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read.',
        ]);
    /**
     * Send a test push notification to all active devices of the authenticated user.
     */
    public function sendTest(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:500'],
            'data' => ['nullable', 'array'],
        ]);

        $title = $validated['title'] ?? 'إشعار تجريبي من راندي 🚀';
        $body = $validated['body'] ?? 'تم إرسال هذا الإشعار التجريبي بنجاح عبر Expo Push Service!';
        $data = $validated['data'] ?? ['type' => 'test', 'timestamp' => now()->toIso8601String()];

        // Find user devices with valid push tokens
        $devices = \App\Models\UserDevice::where('user_id', $user->id)
            ->whereNotNull('push_notification_token')
            ->where('push_notifications_enabled', true)
            ->where('is_active', true)
            ->get();

        if ($devices->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No active devices with push notification tokens found for this user.',
            ], 404);
        }

        $messages = [];
        foreach ($devices as $device) {
            $messages[] = [
                'to' => $device->push_notification_token,
                'sound' => 'default',
                'title' => $title,
                'body' => $body,
                'data' => $data,
            ];
        }

        // Send via Expo Push API
        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'Accept' => 'application/json',
            'Accept-Encoding' => 'gzip, deflate',
            'Content-Type' => 'application/json',
        ])->post('https://exp.host/--/api/v2/push/send', $messages);

        // Optionally record notification in database
        $notification = Notification::create([
            'user_id' => $user->id,
            'title' => $title,
            'body' => $body,
            'type' => 'test',
            'data' => $data,
            'is_read' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Test notification sent.',
            'devices_notified' => $devices->count(),
            'expo_response' => $response->json(),
            'notification' => $notification,
        ]);
    }
}
