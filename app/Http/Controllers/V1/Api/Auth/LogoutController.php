<?php

namespace App\Http\Controllers\V1\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\UserDevice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    /**
     * POST /api/v1/auth/logout
     */
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user) {
            $deviceId = $request->input('device_id');
            $pushToken = $request->input('push_notification_token');

            $query = UserDevice::where('user_id', $user->id);

            if ($deviceId) {
                $query->where('device_id', $deviceId);
            } elseif ($pushToken) {
                $query->where('push_notification_token', $pushToken);
            }

            $query->update([
                'push_notification_token' => null,
                'push_notification_token_sandbox' => null,
                'push_notifications_enabled' => false,
                'is_active' => false,
                'deactivated_at' => now(),
            ]);

            $token = $user->currentAccessToken();
            if ($token) {
                $token->delete();
            }
        }

        return response()->json([
            'message' => 'Logged out successfully and device push token cleared.',
        ]);
    }
}
