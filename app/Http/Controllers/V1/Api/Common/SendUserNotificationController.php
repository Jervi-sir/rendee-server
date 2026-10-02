<?php

namespace App\Http\Controllers\V1\Api\Common;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Notification\PushNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SendUserNotificationController extends Controller
{
    public function __construct(
        protected PushNotificationService $pushService
    ) {}

    /**
     * Send push notification to a specific user by userId.
     *
     * POST /api/v1/notifications/send-to-user
     * or POST /api/v1/users/{userId}/notify
     */
    public function __invoke(Request $request, ?int $userId = null): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:500'],
            'data' => ['nullable', 'array'],
            'type' => ['nullable', 'string', 'max:50'],
            'save_to_db' => ['nullable', 'boolean'],
        ]);

        $targetUserId = $userId ?? $validated['user_id'] ?? null;

        if (! $targetUserId) {
            return response()->json([
                'success' => false,
                'message' => 'The user_id field is required.',
            ], 422);
        }

        $user = User::find($targetUserId);
        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => "User with ID {$targetUserId} not found.",
            ], 404);
        }

        $result = $this->pushService->sendToUser(
            user: $user,
            title: $validated['title'],
            body: $validated['body'],
            data: $validated['data'] ?? [],
            type: $validated['type'] ?? 'general',
            options: [
                'save_to_db' => $validated['save_to_db'] ?? true,
            ]
        );

        return response()->json([
            'success' => $result['success'] ?? false,
            'message' => ($result['success'] ?? false)
                ? 'Notification sent successfully.'
                : 'Notification recorded, but no active push device was reached.',
            'data' => $result,
        ], ($result['success'] ?? false) ? 200 : 200);
    }
}
