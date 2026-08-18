<?php

namespace App\Http\Controllers\V1\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Models\Patient;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RegisterController extends Controller
{
    /**
     * POST /api/v1/auth/register
     *
     * Request JSON:
     * {
     *   "phone_number": "0550000000",
     *   "password": "password123",
     *   "password_confirmation": "password123",
     *   "full_name": "Ahmed Benali",
     *   "user_role_code": "patient",
     *   "partner_type": "doctor",
     *   "email": "ahmed@example.com"
     * }
     *
     * Response JSON:
     * {
     *   "message": "Registration successful",
     *   "token_type": "Bearer",
     *   "access_token": "1|abcdef123456...",
     *   "user": {
     *     "id": 2,
     *     "user_role_code": "patient",
     *     "name": "Ahmed",
     *     "full_name": "Ahmed Benali",
     *     "phone_number": "0550000000",
     *     "profile_completed": false
     *   }
     * }
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_role_code' => ['required', 'string', 'in:patient,partner'],
            'partner_type' => ['nullable', 'string'],
            'name' => ['nullable', 'string', 'max:255'],
            'full_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')],
            'phone_number' => ['nullable', 'string', 'max:50', Rule::unique('users', 'phone_number')],
            'phone' => ['nullable', 'string', 'max:50', Rule::unique('users', 'phone_number')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'profile_completed' => ['nullable', 'boolean'],
        ]);

        $phoneNumber = trim((string) ($validated['phone_number'] ?? $validated['phone'] ?? ''));

        if (! $phoneNumber) {
            return response()->json([
                'message' => 'The phone number field is required.',
                'errors' => ['phone_number' => ['The phone number field is required.']],
            ], 422);
        }

        $roleCode = $validated['user_role_code'];
        $partnerTypeInput = $validated['partner_type'] ?? null;

        // Ensure the role exists in the database
        UserRole::firstOrCreate(
            ['code' => $roleCode],
            [
                'en' => ucfirst($roleCode),
                'fr' => ucfirst($roleCode),
                'ar' => $roleCode,
            ]
        );

        $fullName = $validated['full_name'] ?? null;
        $name = $validated['name'] ?? null;
        $email = $validated['email'] ?? ($phoneNumber . '@rendee.local');

        $user = new User([
            'user_role_code' => $roleCode,
            'name' => $name ?: ($fullName ? Str::before($fullName, ' ') : $phoneNumber),
            'full_name' => $fullName,
            'email' => $email,
            'phone_number' => $phoneNumber,
            'password' => Hash::make($validated['password']),
            'password_plaintext' => $validated['password'],
            'profile_completed' => (bool) ($validated['profile_completed'] ?? false),
        ]);

        $user->save();

        // Create the associated role profile
        if ($roleCode === 'patient') {
            Patient::create(['user_id' => $user->id]);
        } elseif ($roleCode === 'partner') {
            Partner::create([
                'user_id' => $user->id,
                'partner_type_code' => $partnerTypeInput ?? 'doctor',
                'name' => $user->full_name ?? $user->name,
                'is_available' => true,
                'is_active' => true,
            ]);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json($user->formatAuthResponse($token, 'Registration successful'), 201);
    }
}
