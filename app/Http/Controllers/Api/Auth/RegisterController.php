<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Center;
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
            'user_role_code' => ['nullable', 'string', Rule::exists('user_roles', 'code')],
            'name' => ['nullable', 'string', 'max:255'],
            'full_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'phone_number' => ['nullable', 'string', 'max:50'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'profile_complete' => ['nullable', 'boolean'],
        ]);

        $fullName = $validated['full_name'] ?? null;
        $name = $validated['name'] ?? null;
        $email = $validated['email'];

        $user = new User([
            'user_role_code' => $validated['user_role_code'] ?? 'patient',
            'name' => $name ?: ($fullName ? Str::before($fullName, ' ') : Str::before($email, '@')),
            'full_name' => $fullName,
            'email' => $email,
            'phone_number' => $validated['phone_number'] ?? null,
            'password' => Hash::make($validated['password']),
            'profile_complete' => (bool) ($validated['profile_complete'] ?? false),
        ]);

        $user->save();

        // Create the associated role profile
        if ($user->user_role_code === 'patient') {
            Patient::create(['user_id' => $user->id]);
        } elseif ($user->user_role_code === 'doctor') {
            Doctor::create(['user_id' => $user->id, 'is_available' => true]);
        } elseif ($user->user_role_code === 'center') {
            Center::create(['user_id' => $user->id, 'name' => $user->full_name ?? $user->name, 'is_active' => true]);
        }

        $user->load(['userRole', 'userDevice', 'doctor', 'center', 'patient']);

        $token = $user->createToken($request->input('device_name', 'api'))->plainTextToken;

        return response()->json([
            'message' => 'Registered successfully.',
            'token_type' => 'Bearer',
            'access_token' => $token,
            'user' => $user,
        ], 201);
    }
}
