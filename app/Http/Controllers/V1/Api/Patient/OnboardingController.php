<?php

namespace App\Http\Controllers\V1\Api\Patient;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OnboardingController extends Controller
{
    /**
     * GET /api/v1/patient/onboarding
     *
     * Response JSON:
     * {
     *   "success": true,
     *   "is_completed": false,
     *   "completion_percentage": 60,
     *   "user": {
     *     "id": 2,
     *     "name": "Ahmed Benali",
     *     "full_name": "Ahmed Benali",
     *     "email": "patient@rendee.dz",
     *     "phone": "0551111111",
     *     "profile_completed": false,
     *     "image_url": null,
     *     "patient": {
     *       "id": 1,
     *       "date_of_birth": "1992-05-14",
     *       "gender": "male",
     *       "address": "12 Rue Didouche Mourad",
     *       "city": "Alger",
     *       "medical_notes": null,
     *       "blood_type": "O+",
     *       "allergies": ["Pénicilline"],
     *       "chronic_diseases": [],
     *       "medications": [],
     *       "emergency_contacts": []
     *     }
     *   }
     * }
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user() ?? (app()->environment('local', 'testing') ? User::where('user_role_code', 'patient')->first() ?? User::first() : null);

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $user->load(['patient.wilaya']);
        $patient = $user->patient;

        $completionPercentage = $this->calculateCompletionPercentage($patient);

        return response()->json([
            'success' => true,
            'is_completed' => (bool) $user->profile_completed,
            'completion_percentage' => $completionPercentage,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'full_name' => $user->full_name ?? $user->name,
                'email' => $user->email,
                'phone' => $user->phone_number ?? '',
                'profile_completed' => (bool) $user->profile_completed,
                'image_url' => $user->image_url,
                'patient' => $patient ? [
                    'id' => $patient->id,
                    'date_of_birth' => $patient->date_of_birth ? ($patient->date_of_birth instanceof \DateTime ? $patient->date_of_birth->format('Y-m-d') : (string) $patient->date_of_birth) : null,
                    'gender' => $patient->gender,
                    'wilaya_code' => $patient->wilaya_code,
                    'wilaya_name' => $patient->wilaya?->ar ?? $patient->wilaya?->en ?? null,
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
     * POST /api/v1/patient/onboarding
     *
     * Request JSON:
     * {
     *   "date_of_birth": "1992-05-14",
     *   "gender": "male",
     *   "blood_type": "O+",
     *   "phone": "0551111111",
     *   "emergency_phone": "0552222222",
     *   "address": "12 Rue Didouche Mourad",
     *   "city": "Alger",
     *   "allergies": "Pénicilline, Pollen",
     *   "medical_notes": "Aucun antécédent particulier"
     * }
     *
     * Response JSON:
     * {
     *   "success": true,
     *   "message": "Patient profile completed successfully.",
     *   "is_completed": true,
     *   "user": { ... }
     * }
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user() ?? (app()->environment('local', 'testing') ? User::where('user_role_code', 'patient')->first() ?? User::first() : null);

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
            'wilaya_code' => ['nullable', 'string', 'exists:wilayas,code'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'image' => ['nullable'],
            'image_url' => ['nullable', 'string'],
            'allergies' => ['nullable', 'string'],
            'medical_notes' => ['nullable', 'string'],
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('avatars', 'public');
            $user->image_url = '/storage/'.$path;
        } elseif (! empty($validated['image_url'])) {
            $user->image_url = $validated['image_url'];
        } elseif ($request->filled('image') && is_string($request->input('image')) && str_starts_with($request->input('image'), 'data:image')) {
            $imageData = $request->input('image');
            @list($type, $imageData) = explode(';', $imageData);
            @list(, $imageData) = explode(',', $imageData);
            if ($imageData) {
                $filename = 'avatars/'.uniqid('avatar_').'.jpg';
                \Illuminate\Support\Facades\Storage::disk('public')->put($filename, base64_decode($imageData));
                $user->image_url = '/storage/'.$filename;
            }
        }

        if (! empty($validated['phone'])) {
            $user->phone_number = $validated['phone'];
        }
        if (! empty($validated['full_name']) || ! empty($request->input('full_name'))) {
            $user->full_name = $request->input('full_name');
        }
        $user->save();

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
        if (array_key_exists('wilaya_code', $validated)) {
            $patient->wilaya_code = $validated['wilaya_code'];
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

        $user->profile_completed = true;
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
                'profile_completed' => true,
                'image_url' => $user->image_url,
                'patient' => [
                    'id' => $patient->id,
                    'date_of_birth' => $patient->date_of_birth ? ($patient->date_of_birth instanceof \DateTime ? $patient->date_of_birth->format('Y-m-d') : (string) $patient->date_of_birth) : null,
                    'gender' => $patient->gender,
                    'wilaya_code' => $patient->wilaya_code,
                    'wilaya_name' => $patient->wilaya?->ar ?? $patient->wilaya?->en ?? null,
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
