<?php

namespace App\Http\Controllers\V1\Api\Common;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Notification;
use App\Models\User;
use App\Services\Notification\BookingNotificationService;
use App\Services\Notification\PushNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(
        protected PushNotificationService $pushService,
        protected BookingNotificationService $bookingNotificationService
    ) {}

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
     * Send a test push notification (either by user_id or direct push_token).
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
            $result = $this->pushService->sendToTokens(
                tokens: [$validated['push_token']],
                title: $title,
                body: $body,
                data: $data
            );

            return response()->json([
                'success' => $result['success'] ?? false,
                'mode' => 'direct_token',
                'target_token' => $validated['push_token'],
                'message' => 'Test notification dispatched to provided token.',
                'result' => $result,
            ]);
        }

        // 2. Direct user_id option
        $targetUserId = (int) $validated['user_id'];
        $result = $this->pushService->sendToUser(
            user: $targetUserId,
            title: $title,
            body: $body,
            data: $data,
            type: 'test'
        );

        return response()->json([
            'success' => $result['success'] ?? false,
            'message' => $result['success'] ? 'Test notification sent successfully.' : 'Push notification dispatch failed.',
            'details' => $result,
        ], $result['success'] ? 200 : 400);
    }

    /**
     * Broadcast notification to a specific topic or user role.
     *
     * POST /api/v1/notifications/broadcast
     */
    public function broadcast(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'topic' => ['nullable', 'string', 'max:100'],
            'role' => ['nullable', 'string', 'in:patient,partner'],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:500'],
            'data' => ['nullable', 'array'],
        ]);

        $topic = $validated['topic'] ?? 'general';
        $role = $validated['role'] ?? null;
        $data = $validated['data'] ?? [];

        $result = $this->pushService->sendToTopic(
            topic: $topic,
            title: $validated['title'],
            body: $validated['body'],
            data: $data,
            targetRole: $role
        );

        return response()->json([
            'success' => true,
            'topic' => $topic,
            'target_role' => $role,
            'result' => $result,
        ]);
    }

    /**
     * Test a booking notification specifically for a booking ID.
     *
     * POST /api/v1/notifications/test-booking
     */
    public function testBooking(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'booking_id' => ['required', 'integer', 'exists:bookings,id'],
            'event' => ['required', 'string', 'in:created,confirmed,cancelled,proposal,proposal_accepted,completed'],
            'reason' => ['nullable', 'string'],
        ]);

        $booking = Booking::with(['patient.user', 'partner.user'])->findOrFail($validated['booking_id']);

        $res = match ($validated['event']) {
            'created' => $this->bookingNotificationService->notifyNewBookingCreated($booking),
            'confirmed' => $this->bookingNotificationService->notifyBookingConfirmed($booking),
            'cancelled' => $this->bookingNotificationService->notifyBookingCancelled($booking, reason: $validated['reason'] ?? 'Test cancellation', cancelledBy: 'partner'),
            'proposal' => $this->bookingNotificationService->notifyProposalSent($booking),
            'proposal_accepted' => $this->bookingNotificationService->notifyProposalConfirmed($booking),
            'completed' => $this->bookingNotificationService->notifyBookingCompleted($booking),
        };

        return response()->json([
            'success' => true,
            'booking_id' => $booking->id,
            'event' => $validated['event'],
            'dispatch_result' => $res,
        ]);
    }
}
