<?php

namespace App\Http\Controllers\Api\Doctor\M4;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Speciality;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DoctorPersonalDataController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $doctor = null;

        if ($user) {
            $doctor = Doctor::with(['user', 'specialty'])->where('user_id', $user->id)->first();
        }

        if (!$doctor) {
            $doctor = Doctor::with(['user', 'specialty'])->first();
        }

        $specialities = Speciality::all()->map(function ($s) {
            return [
                'id' => $s->id,
                'label' => $s->ar ?? $s->en ?? $s->code,
            ];
        });

        if (!$doctor) {
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
                'id' => $doctor->id,
                'full_name' => $doctor->user->full_name ?? $doctor->user->name ?? '',
                'email' => $doctor->user->email ?? '',
                'phone' => $doctor->user->phone_number ?? '',
                'specialty_id' => $doctor->specialty?->id,
                'speciality' => $doctor->specialty?->ar ?? $doctor->specialty?->en ?? 'طبيب عام',
                'license_number' => $doctor->license_number,
                'years_experience' => $doctor->years_experience,
                'phone_public' => $doctor->phone_public,
                'bio' => $doctor->bio,
                'address' => $doctor->address,
                'city' => $doctor->city,
                'is_available' => (bool)$doctor->is_available,
            ],
            'specialities' => $specialities,
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $user = $request->user();
        $doctor = null;

        if ($user) {
            $doctor = Doctor::where('user_id', $user->id)->first();
        }

        if (!$doctor) {
            $doctor = Doctor::first();
            if ($doctor) {
                $user = User::find($doctor->user_id);
            }
        }

        if (!$doctor || !$user) {
            return response()->json(['error' => 'Doctor profile not found'], 404);
        }

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:30',
            'specialty_id' => 'nullable|integer',
            'license_number' => 'nullable|string|max:100',
            'years_experience' => 'nullable|string|max:10',
            'phone_public' => 'nullable|string|max:30',
            'bio' => 'nullable|string',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'is_available' => 'required|boolean',
        ]);

        // Update User
        $user->full_name = $validated['full_name'];
        $user->email = $validated['email'];
        $user->phone_number = $validated['phone'];
        $user->save();

        // Find speciality code
        $specialityCode = null;
        if (!empty($validated['specialty_id'])) {
            $speciality = Speciality::find($validated['specialty_id']);
            if ($speciality) {
                $specialityCode = $speciality->code;
            }
        }

        // Update Doctor
        $doctor->speciality_code = $specialityCode;
        $doctor->license_number = $validated['license_number'];
        $doctor->years_experience = $validated['years_experience'];
        $doctor->phone_public = $validated['phone_public'];
        $doctor->bio = $validated['bio'];
        $doctor->address = $validated['address'];
        $doctor->city = $validated['city'];
        $doctor->is_available = $validated['is_available'];
        $doctor->save();

        // Check if profile is complete
        $isComplete = !empty($doctor->speciality_code) &&
                      !empty($doctor->license_number) &&
                      !empty($doctor->years_experience) &&
                      !empty($doctor->bio) &&
                      !empty($doctor->address) &&
                      !empty($doctor->city) &&
                      !empty($doctor->phone_public) &&
                      $doctor->is_available;

        $user->profile_complete = $isComplete;
        $user->save();

        return response()->json([
            'success' => true,
            'profile_complete' => $isComplete,
        ]);
    }
}
