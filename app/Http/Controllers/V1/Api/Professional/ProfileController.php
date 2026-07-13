<?php

namespace App\Http\Controllers\V1\Api\Professional;

use App\Http\Controllers\Controller;
use App\Models\Professional;
use App\Models\ProfessionalSpeciality;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Retrieve the authenticated professional's profile.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $professional = null;

        if ($user) {
            $professional = Professional::with(['user', 'specialty'])->where('user_id', $user->id)->first();
        }

        if (!$professional) {
            $professional = Professional::with(['user', 'specialty'])->first();
        }

        $specialities = ProfessionalSpeciality::all()->map(function ($s) {
            return [
                'id' => $s->id,
                'label' => $s->ar ?? $s->en ?? $s->code,
                'code' => $s->code,
            ];
        });

        if (!$professional) {
            return response()->json([
                'profile' => [
                    'full_name' => '',
                    'email' => '',
                    'phone' => '',
                    'specialty_id' => null,
                    'license_number' => '',
                    'years_experience' => '',
                    'phone_public' => '',
                    'bio' => '',
                    'address' => '',
                    'city' => '',
                    'is_available' => true,
                ],
                'specialities' => $specialities,
            ]);
        }

        return response()->json([
            'profile' => [
                'id' => $professional->id,
                'full_name' => $professional->user->full_name ?? $professional->user->name ?? '',
                'email' => $professional->user->email ?? '',
                'phone' => $professional->user->phone_number ?? '',
                'specialty_id' => $professional->specialty?->id,
                'speciality' => $professional->specialty?->ar ?? $professional->specialty?->en ?? 'عام',
                'license_number' => $professional->license_number,
                'years_experience' => $professional->years_experience,
                'phone_public' => $professional->phone_public,
                'bio' => $professional->bio,
                'address' => $professional->address,
                'city' => $professional->city,
                'is_available' => (bool) $professional->is_available,
                'profession_code' => $professional->profession_code,
            ],
            'specialities' => $specialities,
        ]);
    }

    /**
     * Update the authenticated professional's profile.
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();
        $professional = null;

        if ($user) {
            $professional = Professional::where('user_id', $user->id)->first();
        }

        if (!$professional) {
            $professional = Professional::first();
            if ($professional) {
                $user = User::find($professional->user_id);
            }
        }

        if (!$professional || !$user) {
            return response()->json(['error' => 'Professional profile not found'], 404);
        }

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:30'],
            'specialty_id' => ['nullable', 'integer'],
            'license_number' => ['nullable', 'string', 'max:100'],
            'years_experience' => ['nullable', 'string', 'max:10'],
            'phone_public' => ['nullable', 'string', 'max:30'],
            'bio' => ['nullable', 'string'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'is_available' => ['required', 'boolean'],
        ]);

        // Update User account details
        $user->full_name = $validated['full_name'];
        $user->email = $validated['email'];
        $user->phone_number = $validated['phone'];
        $user->save();

        // Resolve speciality code
        $specialityCode = null;
        if (!empty($validated['specialty_id'])) {
            $speciality = ProfessionalSpeciality::find($validated['specialty_id']);
            if ($speciality) {
                $specialityCode = $speciality->code;
            }
        }

        // Update Professional specific details
        $professional->speciality_code = $specialityCode;
        $professional->license_number = $validated['license_number'];
        $professional->years_experience = $validated['years_experience'];
        $professional->phone_public = $validated['phone_public'];
        $professional->bio = $validated['bio'];
        $professional->address = $validated['address'];
        $professional->city = $validated['city'];
        $professional->is_available = $validated['is_available'];
        $professional->save();

        // Check if profile is complete
        $isComplete = !empty($professional->speciality_code) &&
                      !empty($professional->license_number) &&
                      !empty($professional->years_experience) &&
                      !empty($professional->bio) &&
                      !empty($professional->address) &&
                      !empty($professional->city) &&
                      !empty($professional->phone_public);

        $user->profile_complete = $isComplete;
        $user->save();

        return response()->json([
            'success' => true,
            'profile_complete' => $isComplete,
            'professional' => $professional->load(['user', 'specialty']),
        ]);
    }
}
