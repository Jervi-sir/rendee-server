<?php

namespace App\Http\Controllers\V1\Api\Patient;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OnboardingController extends Controller
{
    /**
     * Get patient onboarding status and existing profile data.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $user->load('patient');
        $patient = $user->patient;

        $completionPercentage = $this->calculateCompletionPercentage($patient);

        return response()->json([
            'success' => true,
            'is_completed' => (bool) $user->profile_complete,
            'completion_percentage' => $completionPercentage,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'full_name' => $user->full_name ?? $user->name,
                'email' => $user->email,
                'phone' => $user->phone_number ?? '',
                'profile_complete' => (bool) $user->profile_complete,
                'image_url' => $user->image_url,
                'patient' => $patient ? [
                    'id' => $patient->id,
                    'date_of_birth' => $patient->date_of_birth ? ($patient->date_of_birth instanceof \DateTime ? $patient->date_of_birth->format('Y-m-d') : (string) $patient->date_of_birth) : null,
                    'gender' => $patient->gender,
                    'address' => $patient->address,
                    'city' => $patient->city,
                    'medical_notes' => $patient->medical_notes,
                    'blood_type' => $patient->blood_type ?? 'O+',
                    'allergies' => $patient->allergies ?? [],
                    'chronic_diseases' => $patient->chronic_diseases ?? [],
                    'medications' => $patient->medications ?? [],
                    'emergency_contacts' => $patient->emergency_contacts ?? [],
                ] : null,
            ],
        ]);
    }

    /**
     * Complete or update patient onboarding profile information.
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $validated = $request->validate([
            'date_of_birth' => ['required', 'string'],
            'gender' => ['required', 'in:male,female'],
            'blood_type' => ['nullable', 'string', 'max:10'],
            'phone' => ['nullable', 'string', 'max:30'],
            'emergency_phone' => ['nullable', 'string', 'max:30'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'allergies' => ['nullable', 'string'],
            'medical_notes' => ['nullable', 'string'],
        ]);

        if (! empty($validated['phone'])) {
            $user->phone_number = $validated['phone'];
            $user->save();
        }

        $patient = $user->patient;
        if (! $patient) {
            $patient = new Patient;
            $patient->user_id = $user->id;
        }

        $patient->date_of_birth = $validated['date_of_birth'];
        $patient->gender = $validated['gender'];
        if (! empty($validated['blood_type'])) {
            $patient->blood_type = $validated['blood_type'];
        }
        $patient->address = $validated['address'];
        $patient->city = $validated['city'];
        if (isset($validated['medical_notes'])) {
            $patient->medical_notes = $validated['medical_notes'];
        }

        if (! empty($validated['allergies'])) {
            $existingAllergies = is_array($patient->allergies) ? $patient->allergies : [];
            $newAllergies = array_filter(array_map('trim', explode(',', $validated['allergies'])));
            $patient->allergies = array_values(array_unique(array_merge($existingAllergies, $newAllergies)));
        }

        if (! empty($validated['emergency_phone'])) {
            $existingContacts = is_array($patient->emergency_contacts) ? $patient->emergency_contacts : [];
            $existingContacts[] = [
                'id' => (string) time(),
                'name' => 'جهة اتصال طوارئ',
                'relation' => 'طوارئ',
                'phone' => $validated['emergency_phone'],
            ];
            $patient->emergency_contacts = $existingContacts;
        }

        $patient->save();

        $user->profile_complete = true;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Patient profile completed successfully.',
            'is_completed' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'full_name' => $user->full_name ?? $user->name,
                'email' => $user->email,
                'phone' => $user->phone_number ?? '',
                'profile_complete' => true,
                'image_url' => $user->image_url,
                'patient' => [
                    'id' => $patient->id,
                    'date_of_birth' => $patient->date_of_birth ? ($patient->date_of_birth instanceof \DateTime ? $patient->date_of_birth->format('Y-m-d') : (string) $patient->date_of_birth) : null,
                    'gender' => $patient->gender,
                    'address' => $patient->address,
                    'city' => $patient->city,
                    'medical_notes' => $patient->medical_notes,
                    'blood_type' => $patient->blood_type ?? 'O+',
                    'allergies' => $patient->allergies ?? [],
                    'chronic_diseases' => $patient->chronic_diseases ?? [],
                    'medications' => $patient->medications ?? [],
                    'emergency_contacts' => $patient->emergency_contacts ?? [],
                ],
            ],
        ]);
    }

    /**
     * Calculate patient profile completion percentage.
     */
    private function calculateCompletionPercentage(?Patient $patient): int
    {
        if (! $patient) {
            return 20;
        }

        $totalFields = 5;
        $filled = 0;

        if (! empty($patient->date_of_birth)) {
            $filled++;
        }
        if (! empty($patient->gender)) {
            $filled++;
        }
        if (! empty($patient->address)) {
            $filled++;
        }
        if (! empty($patient->city)) {
            $filled++;
        }
        if (! empty($patient->medical_notes) || ! empty($patient->allergies)) {
            $filled++;
        }

        return (int) round(($filled / $totalFields) * 100);
    }
}
