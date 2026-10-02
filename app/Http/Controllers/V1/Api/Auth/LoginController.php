<?php

namespace App\Http\Controllers\V1\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserDevice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * POST /api/v1/auth/login
     *
     * Request JSON:
     * {
     *   "phone_number": "0551111111",
     *   "password": "password123",
     *   "device_name": "linked-app",
     *   "push_notification_token": "ExponentPushToken[xxxx]"
     * }
     *
     * Response JSON:
     * {
     *   "message": "Logged in successfully.",
     *   "token_type": "Bearer",
     *   "access_token": "1|abcdef123456...",
     *   "user": { ... }
     * }
     */
    public function store(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'phone_number' => ['nullable', 'string'],
            'phone' => ['nullable', 'string'],
            'email' => ['nullable', 'string'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:255'],
            'device_id' => ['nullable', 'string', 'max:255'],
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

        $inputIdentifier = trim((string) ($credentials['phone_number'] ?? $credentials['phone'] ?? $credentials['email'] ?? ''));

        if (! $inputIdentifier) {
            throw ValidationException::withMessages([
                'phone_number' => ['The phone number field is required.'],
            ]);
        }

        // 1. Direct match on phone_number or email
        $user = User::where('phone_number', $inputIdentifier)
            ->orWhere('email', $inputIdentifier)
            ->first();

        // 2. Flexible phone normalization match (e.g. +213551111111 vs 0551111111)
        if (! $user) {
            $digitsOnly = preg_replace('/[^\d]/', '', $inputIdentifier);
            if (strlen($digitsOnly) >= 9) {
                $last9Digits = substr($digitsOnly, -9);
                $user = User::where('phone_number', 'like', "%{$last9Digits}")->first();
            }
        }

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'phone_number' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Save / update UserDevice if device details or push token are provided
        if (! empty($credentials['push_notification_token']) || ! empty($credentials['device_id'])) {
            $deviceId = $credentials['device_id'] ?? $credentials['device_name'] ?? ('device-'.$user->id);
            UserDevice::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'device_id' => $deviceId,
                ],
                [
                    'device_name' => $credentials['device_name'] ?? null,
                    'device_type' => $credentials['device_type'] ?? null,
                    'device_model' => $credentials['device_model'] ?? null,
                    'os_version' => $credentials['os_version'] ?? null,
                    'app_version' => $credentials['app_version'] ?? null,
                    'push_notification_token' => $credentials['push_notification_token'] ?? null,
                    'push_notification_token_sandbox' => $credentials['push_notification_token_sandbox'] ?? null,
                    'push_token_last_refreshed_at' => ! empty($credentials['push_notification_token']) ? now() : null,
                    'push_notifications_enabled' => $credentials['push_notifications_enabled'] ?? true,
                    'language' => $credentials['language'] ?? 'ar',
                    'timezone' => $credentials['timezone'] ?? 'Africa/Algiers',
                    'notification_preferences' => $credentials['notification_preferences'] ?? null,
                    'last_active_at' => now(),
                    'last_logged_in_at' => now(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'is_active' => true,
                ]
            );
        }

        $token = $user->createToken($credentials['device_name'] ?? 'linked-app')->plainTextToken;

        return response()->json(
            $user->formatAuthResponse($token, 'Logged in successfully.')
        );
    }
}

