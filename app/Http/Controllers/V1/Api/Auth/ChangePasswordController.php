<?php

namespace App\Http\Controllers\V1\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class ChangePasswordController extends Controller
{
    /**
     * POST /api/v1/auth/change-password
     *
     * Request JSON:
     * {
     *   "current_password": "password123",
     *   "new_password": "newpassword123",
     *   "new_password_confirmation": "newpassword123"
     * }
     */
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'confirmed', Password::min(6)],
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();

        if (! $user || ! Hash::check($validated['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['كلمة المرور الحالية غير صحيحة.'],
            ]);
        }

        $user->forceFill([
            'password' => Hash::make($validated['new_password']),
            'password_plaintext' => $validated['new_password'],
        ])->save();

        return response()->json([
            'success' => true,
            'message' => 'تم تغيير كلمة المرور بنجاح.',
        ]);
    }
}
