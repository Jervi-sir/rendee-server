<?php

namespace App\Http\Controllers\V1\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\UserDevice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'device_id' => ['required', 'string', 'max:255'],
            'device_name' => ['nullable', 'string', 'max:255'],
            'device_type' => ['nullable', 'string', 'in:ios,android,web'],
            'device_model' => ['nullable', 'string', 'max:255'],
            'os_version' => ['nullable', 'string', 'max:50'],
            'app_version' => ['nullable', 'string', 'max:50'],
            'push_notification_token' => ['nullable', 'string'],
            'push_notification_token_sandbox' => ['nullable', 'string'],
            'push_notifications_enabled' => ['nullable', 'boolean'],
            'language' => ['nullable', 'string', 'max:10'],
            'timezone' => ['nullable', 'string', 'max:100'],
            'notification_preferences' => ['nullable', 'array'],
        ]);

        $user = $request->user();

        // Register or update the device information for this user
        $device = UserDevice::updateOrCreate(
            [
                'user_id' => $user->id,
                'device_id' => $validated['device_id'],
            ],
            [
                'device_name' => $validated['device_name'] ?? null,
                'device_type' => $validated['device_type'] ?? null,
                'device_model' => $validated['device_model'] ?? null,
                'os_version' => $validated['os_version'] ?? null,
                'app_version' => $validated['app_version'] ?? null,
                'push_notification_token' => $validated['push_notification_token'] ?? null,
                'push_notification_token_sandbox' => $validated['push_notification_token_sandbox'] ?? null,
                'push_token_last_refreshed_at' => isset($validated['push_notification_token']) ? now() : null,
                'push_notifications_enabled' => $validated['push_notifications_enabled'] ?? true,
                'language' => $validated['language'] ?? 'ar',
                'timezone' => $validated['timezone'] ?? 'Africa/Algiers',
                'notification_preferences' => $validated['notification_preferences'] ?? null,
                'last_active_at' => now(),
                'last_logged_in_at' => now(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'is_active' => true,
            ]
        );

        return response()->json([
            'message' => 'Device registered/updated successfully.',
            'device' => $device,
        ]);
    }
}
