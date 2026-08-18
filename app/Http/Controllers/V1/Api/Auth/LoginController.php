<?php

namespace App\Http\Controllers\V1\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
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
     *   "device_name": "linked-app"
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

        $token = $user->createToken($credentials['device_name'] ?? 'linked-app')->plainTextToken;

        return response()->json(
            $user->formatAuthResponse($token, 'Logged in successfully.')
        );
    }
}
