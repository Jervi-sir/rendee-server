<?php

namespace App\Http\Controllers\V1\Api\Common;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use App\Models\UserDevice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

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
    }

    /**
     * Send a test push notification without requiring auth:
     * - Requires only `user_id` (or fallback to `push_token`)
     *
     * POST /api/v1/notifications/test
     */
    public function sendTest(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => ['required_without:push_token', 'nullable', 'integer', 'exists:users,id'],
            'push_token' => ['required_without:user_id', 'nullable', 'string'],
            'title' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:500'],
            'data' => ['nullable', 'array'],
        ]);

        $title = $validated['title'] ?? 'إشعار تجريبي من راندي 🚀';
        $body = $validated['body'] ?? 'تم إرسال هذا الإشعار التجريبي بنجاح عبر خدمة Expo Push!';
        $data = $validated['data'] ?? [
            'type' => 'test',
            'timestamp' => now()->toIso8601String(),
            'url' => 'rendee://notifications',
        ];

        // 1. Direct Push Token option
        if (! empty($validated['push_token'])) {
            $messages = [
                [
                    'to' => $validated['push_token'],
                    'sound' => 'default',
                    'title' => $title,
                    'body' => $body,
                    'data' => $data,
                    'channelId' => 'default',
                    'priority' => 'high',
                ],
            ];

            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Accept-Encoding' => 'gzip, deflate',
                'Content-Type' => 'application/json',
            ])->post('https://exp.host/--/api/v2/push/send', $messages);

            return response()->json([
                'success' => true,
                'mode' => 'direct_token',
                'target_token' => $validated['push_token'],
                'message' => 'Test notification dispatched to provided token.',
                'expo_response' => $response->json(),
            ]);
        }

        // 2. Direct user_id option (no authentication required)
        $targetUserId = (int) $validated['user_id'];

        $devices = UserDevice::where('user_id', $targetUserId)
            ->whereNotNull('push_notification_token')
            ->where('push_notifications_enabled', true)
            ->where('is_active', true)
            ->get();

        if ($devices->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => "No active devices with registered push tokens found for user ID: {$targetUserId}.",
                'user_id' => $targetUserId,
                'tip' => 'Make sure the user has logged in and allowed notifications in the app.',
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
                'channelId' => 'default',
                'priority' => 'high',
            ];
        }

        // Send via Expo Push API
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Accept-Encoding' => 'gzip, deflate',
            'Content-Type' => 'application/json',
        ])->post('https://exp.host/--/api/v2/push/send', $messages);

        // Record notification in DB
        $notification = Notification::create([
            'user_id' => $targetUserId,
            'title' => $title,
            'body' => $body,
            'type' => 'test',
            'data' => $data,
            'is_read' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Test notification sent successfully.',
            'target_user_id' => $targetUserId,
            'devices_notified' => $devices->count(),
            'tokens' => $devices->pluck('push_notification_token'),
            'expo_response' => $response->json(),
            'notification' => $notification,
        ]);
    }
}
