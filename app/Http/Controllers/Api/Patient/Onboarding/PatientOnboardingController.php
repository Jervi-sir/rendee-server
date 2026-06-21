<?php

namespace App\Http\Controllers\Api\Patient\Onboarding;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PatientOnboardingController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.'
            ], 401);
        }

        $user->load('patient');
        return response()->json([
            'user' => [
                'id' => $user->id,
                'full_name' => $user->full_name ?? $user->name,
                'email' => $user->email,
                'phone' => $user->phone_number ?? $user->phone ?? '',
                'profile_complete' => (bool)$user->profile_complete,
                'patient' => $user->patient ? [
                    'date_of_birth' => $user->patient->date_of_birth ? ($user->patient->date_of_birth instanceof \DateTime ? $user->patient->date_of_birth->format('Y-m-d') : $user->patient->date_of_birth) : null,
                    'gender' => $user->patient->gender,
                    'address' => $user->patient->address,
                    'city' => $user->patient->city,
                    'medical_notes' => $user->patient->medical_notes,
                ] : null,
            ]
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date_of_birth' => ['required', 'date_format:Y-m-d'],
            'gender' => ['required', 'in:male,female'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'medical_notes' => ['required', 'string'],
        ]);

        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.'
            ], 401);
        }

        $patient = $user->patient;
        if (!$patient) {
            $patient = new Patient();
            $patient->user_id = $user->id;
        }

        $patient->fill($validated);
        $patient->save();

        $user->profile_complete = true;
        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => [
                'id' => $user->id,
                'full_name' => $user->full_name ?? $user->name,
                'email' => $user->email,
                'phone' => $user->phone_number ?? $user->phone ?? '',
                'profile_complete' => true,
                'patient' => [
                    'date_of_birth' => $patient->date_of_birth ? ($patient->date_of_birth instanceof \DateTime ? $patient->date_of_birth->format('Y-m-d') : $patient->date_of_birth) : null,
                    'gender' => $patient->gender,
                    'address' => $patient->address,
                    'city' => $patient->city,
                    'medical_notes' => $patient->medical_notes,
                ],
            ]
        ]);
    }
}
