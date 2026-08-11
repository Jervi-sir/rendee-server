<?php

namespace App\Http\Controllers\V1\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\Center;
use App\Models\Patient;
use App\Models\Pharmacy;
use App\Models\Professional;
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
            'user_role_code' => ['required', 'string', 'in:patient,center,pharmacist,pharmacy,professional,doctor,psychologist,dentist'],
            'profession_code' => ['nullable', 'string', 'in:doctor,psychologist,dentist'],
            'name' => ['nullable', 'string', 'max:255'],
            'full_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'phone_number' => ['nullable', 'string', 'max:50'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'profile_complete' => ['nullable', 'boolean'],
        ]);

        $roleCode = $validated['user_role_code'];
        $professionCode = $validated['profession_code'] ?? null;

        // Map doctor/psychologist/dentist directly to professional role and set profession code
        if (in_array($roleCode, ['doctor', 'psychologist', 'dentist'])) {
            $professionCode = $roleCode;
            $roleCode = 'professional';
        }

        // If the role is professional, default to 'doctor' if no profession code was provided
        if ($roleCode === 'professional' && empty($professionCode)) {
            $professionCode = 'doctor';
        }

        // Normalize pharmacy to pharmacist
        if ($roleCode === 'pharmacy') {
            $roleCode = 'pharmacist';
        }

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
            'profile_complete' => (bool) ($validated['profile_complete'] ?? false),
        ]);

        $user->save();

        // Create the associated role profile
        if ($roleCode === 'patient') {
            Patient::create(['user_id' => $user->id]);
        } elseif ($roleCode === 'professional') {
            Professional::create([
                'user_id' => $user->id,
                'profession_code' => $professionCode,
                'is_available' => true,
            ]);
        } elseif ($roleCode === 'center') {
            Center::create([
                'user_id' => $user->id,
                'name' => $user->full_name ?? $user->name,
                'is_active' => true,
            ]);
        } elseif ($roleCode === 'pharmacist') {
            Pharmacy::create([
                'user_id' => $user->id,
                'name' => $user->full_name ?? $user->name,
                'is_available' => true,
            ]);
        }

        $token = $user->createToken($request->input('device_name', 'api'))->plainTextToken;

        return response()->json(
            $user->formatAuthResponse($token, 'Registered successfully.'),
            201
        );
    }
}
