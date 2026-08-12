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
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_role_code' => ['required', 'string', 'in:patient,partner'],
            'partner_type' => ['nullable', 'string'],
            'name' => ['nullable', 'string', 'max:255'],
            'full_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'phone_number' => ['nullable', 'string', 'max:50'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'profile_completed' => ['nullable', 'boolean'],
        ]);

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
        $email = $validated['email'];

        $user = new User([
            'user_role_code' => $roleCode,
            'name' => $name ?: ($fullName ? Str::before($fullName, ' ') : Str::before($email, '@')),
            'full_name' => $fullName,
            'email' => $email,
            'phone_number' => $validated['phone_number'] ?? null,
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
