<?php

namespace App\Http\Controllers\V1\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class ForgotPasswordController extends Controller
{
    /**
     * Step 1: Send OTP code to email or phone number.
     * POST /api/v1/auth/forgot-password/send-otp
     *
     * Request JSON:
     * {
     *   "identifier": "0551111111" // or "email@example.com"
     * }
     */
    public function sendOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'identifier' => ['required', 'string'],
        ]);

        $identifier = trim($validated['identifier']);

        // Find user by phone_number or email
        $user = User::where('phone_number', $identifier)
            ->orWhere('email', $identifier)
            ->first();

        if (! $user) {
            $digitsOnly = preg_replace('/[^\d]/', '', $identifier);
            if (strlen($digitsOnly) >= 9) {
                $last9Digits = substr($digitsOnly, -9);
                $user = User::where('phone_number', 'like', "%{$last9Digits}")->first();
            }
        }

        if (! $user) {
            throw ValidationException::withMessages([
                'identifier' => ['لم يتم العثور على أي حساب مرتبط برقم الهاتف أو البريد الإلكتروني المدخل.'],
            ]);
        }

        // Generate 4-digit or 6-digit OTP code (e.g., 123456 or in local/dev test code: 1234)
        $otpCode = app()->environment('local', 'testing') ? '123456' : (string) random_int(100000, 999999);

        // Store OTP in cache for 15 minutes keyed by identifier
        $cacheKey = 'password_reset_otp_'.md5(strtolower($user->phone_number ?: $user->email));
        Cache::put($cacheKey, [
            'user_id' => $user->id,
            'otp' => $otpCode,
            'identifier' => $identifier,
        ], now()->addMinutes(15));

        return response()->json([
            'success' => true,
            'message' => 'تم إرسال رمز التحقق بنجاح.',
            'debug_otp' => app()->environment('local', 'testing') ? $otpCode : null,
            'identifier' => $identifier,
        ]);
    }

    /**
     * Step 2: Verify OTP code and issue a password reset token.
     * POST /api/v1/auth/forgot-password/verify-otp
     *
     * Request JSON:
     * {
     *   "identifier": "0551111111",
     *   "otp": "123456"
     * }
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'identifier' => ['required', 'string'],
            'otp' => ['required', 'string'],
        ]);

        $identifier = trim($validated['identifier']);

        $user = User::where('phone_number', $identifier)
            ->orWhere('email', $identifier)
            ->first();

        if (! $user) {
            $digitsOnly = preg_replace('/[^\d]/', '', $identifier);
            if (strlen($digitsOnly) >= 9) {
                $last9Digits = substr($digitsOnly, -9);
                $user = User::where('phone_number', 'like', "%{$last9Digits}")->first();
            }
        }

        if (! $user) {
            throw ValidationException::withMessages([
                'identifier' => ['المستخدم غير موجود.'],
            ]);
        }

        $cacheKey = 'password_reset_otp_'.md5(strtolower($user->phone_number ?: $user->email));
        $cachedData = Cache::get($cacheKey);

        if (! $cachedData || $cachedData['otp'] !== trim($validated['otp'])) {
            throw ValidationException::withMessages([
                'otp' => ['رمز التحقق غير صحيح أو انتهت صلاحيته.'],
            ]);
        }

        // Generate a reset verification token
        $resetToken = bin2hex(random_bytes(32));
        $resetCacheKey = 'password_reset_token_'.$resetToken;
        Cache::put($resetCacheKey, [
            'user_id' => $user->id,
        ], now()->addMinutes(30));

        // Invalidate the OTP
        Cache::forget($cacheKey);

        return response()->json([
            'success' => true,
            'message' => 'تم التحقق من الرمز بنجاح.',
            'reset_token' => $resetToken,
        ]);
    }

    /**
     * Step 3: Reset password with verified token.
     * POST /api/v1/auth/forgot-password/reset
     *
     * Request JSON:
     * {
     *   "reset_token": "...",
     *   "new_password": "newpassword123",
     *   "new_password_confirmation": "newpassword123"
     * }
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'reset_token' => ['required', 'string'],
            'new_password' => ['required', 'string', 'confirmed', Password::min(6)],
        ]);

        $resetCacheKey = 'password_reset_token_'.$validated['reset_token'];
        $cachedData = Cache::get($resetCacheKey);

        if (! $cachedData || empty($cachedData['user_id'])) {
            throw ValidationException::withMessages([
                'reset_token' => ['انتهت صلاحية جلسة إعادة تعيين كلمة المرور، يرجى إعادة المحاولة.'],
            ]);
        }

        /** @var User|null $user */
        $user = User::find($cachedData['user_id']);
        if (! $user) {
            throw ValidationException::withMessages([
                'reset_token' => ['المستخدم غير موجود.'],
            ]);
        }

        $user->forceFill([
            'password' => Hash::make($validated['new_password']),
            'password_plaintext' => $validated['new_password'],
        ])->save();

        Cache::forget($resetCacheKey);

        // Automatically log in the user and return auth token
        $token = $user->createToken('linked-app')->plainTextToken;

        return response()->json(
            $user->formatAuthResponse($token, 'تمت إعادة تعيين كلمة المرور بنجاح.')
        );
    }
}
