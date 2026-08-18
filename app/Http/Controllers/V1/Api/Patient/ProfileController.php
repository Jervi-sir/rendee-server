<?php

namespace App\Http\Controllers\V1\Api\Patient;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * GET /api/v1/patient/profile
     *
     * Response JSON:
     * {
     *   "user": {
     *     "id": 2,
     *     "name": "Ahmed Benali",
     *     "full_name": "Ahmed Benali",
     *     "email": "patient@rendee.dz",
     *     "phone": "0551111111",
     *     "profile_completed": true,
     *     "image_url": null,
     *     "bookings_count": 3,
     *     "searches_count": 0,
     *     "files_count": 0,
     *     "patient": {
     *       "id": 1,
     *       "date_of_birth": "1992-05-14",
     *       "gender": "male",
     *       "address": "12 Rue Didouche Mourad",
     *       "city": "Alger",
     *       "medical_notes": "Pas de contre-indications",
     *       "blood_type": "O+",
     *       "allergies": ["Pénicilline"],
     *       "chronic_diseases": [],
     *       "medications": [],
     *       "emergency_contacts": [
     *         {
     *           "name": "Karim Benali",
     *           "relation": "Frère",
     *           "phone": "0552222222"
     *         }
     *       ]
     *     }
     *   }
     * }
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user() ?? (app()->environment('local', 'testing') ? User::where('user_role_code', 'patient')->first() ?? User::first() : null);

        if (! $user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $user->load('patient');
        $bookingsCount = $user->patient ? $user->patient->bookings()->count() : 0;

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'full_name' => $user->full_name ?? $user->name,
                'email' => $user->email,
                'phone' => $user->phone_number ?? '',
                'profile_completed' => (bool) $user->profile_completed,
                'image_url' => $user->image_url,
                'bookings_count' => $bookingsCount,
                'searches_count' => 0,
                'files_count' => 0,
                'patient' => $user->patient ? [
                    'id' => $user->patient->id,
                    'date_of_birth' => $user->patient->date_of_birth ? ($user->patient->date_of_birth instanceof \DateTime ? $user->patient->date_of_birth->format('Y-m-d') : (string) $user->patient->date_of_birth) : null,
                    'gender' => $user->patient->gender,
                    'address' => $user->patient->address,
                    'city' => $user->patient->city,
                    'medical_notes' => $user->patient->medical_notes,
                    'blood_type' => $user->patient->blood_type ?? 'O+',
                    'allergies' => $user->patient->allergies ?? [],
                    'chronic_diseases' => $user->patient->chronic_diseases ?? [],
                    'medications' => $user->patient->medications ?? [],
                    'emergency_contacts' => $user->patient->emergency_contacts ?? [],
                ] : null,
            ],
        ]);
    }

    /**
     * PUT /api/v1/patient/profile
     *
     * Request JSON:
     * {
     *   "full_name": "Ahmed Benali",
     *   "phone": "0551111111",
     *   "date_of_birth": "1992-05-14",
     *   "gender": "male",
     *   "blood_type": "O+",
     *   "address": "12 Rue Didouche Mourad",
     *   "city": "Alger",
     *   "medical_notes": "Pas de contre-indications",
     *   "allergies": ["Pénicilline", "Pollen"],
     *   "chronic_diseases": ["Asthme"],
     *   "medications": ["Ventoline"],
     *   "emergency_contacts": [
     *     {
     *       "name": "Karim Benali",
     *       "relation": "Frère",
     *       "phone": "0552222222"
     *     }
     *   ]
     * }
     *
     * Response JSON:
     * {
     *   "message": "Profile updated successfully.",
     *   "user": { ... }
     * }
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'full_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'date_of_birth' => ['nullable', 'string'],
            'gender' => ['nullable', 'in:male,female'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'medical_notes' => ['nullable', 'string'],
            'blood_type' => ['nullable', 'string', 'max:10'],
            'allergies' => ['nullable', 'array'],
            'allergies.*' => ['string'],
            'chronic_diseases' => ['nullable', 'array'],
            'chronic_diseases.*' => ['string'],
            'medications' => ['nullable', 'array'],
            'medications.*' => ['string'],
            'emergency_contacts' => ['nullable', 'array'],
            'emergency_contacts.*.id' => ['nullable', 'string'],
            'emergency_contacts.*.name' => ['required_with:emergency_contacts', 'string'],
            'emergency_contacts.*.relation' => ['nullable', 'string'],
            'emergency_contacts.*.phone' => ['required_with:emergency_contacts', 'string'],
        ]);

        $user = $request->user() ?? (app()->environment('local', 'testing') ? User::where('user_role_code', 'patient')->first() ?? User::first() : null);

        if (! $user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        // Update User info
        if (isset($validated['full_name'])) {
            $user->full_name = $validated['full_name'];
        }
        if (isset($validated['phone'])) {
            $user->phone_number = $validated['phone'];
        }
        $user->save();

        // Find or create Patient profile
        $patient = $user->patient;
        if (! $patient) {
            $patient = new Patient;
            $patient->user_id = $user->id;
        }

        if (array_key_exists('date_of_birth', $validated)) {
            $dob = $validated['date_of_birth'];
            if ($dob && strlen($dob) > 10) {
                $dob = substr($dob, 0, 10);
            }
            $patient->date_of_birth = $dob;
        }
        if (array_key_exists('gender', $validated)) {
            $patient->gender = $validated['gender'];
        }
        if (array_key_exists('address', $validated)) {
            $patient->address = $validated['address'];
        }
        if (array_key_exists('city', $validated)) {
            $patient->city = $validated['city'];
        }
        if (array_key_exists('medical_notes', $validated)) {
            $patient->medical_notes = $validated['medical_notes'];
        }
        if (array_key_exists('blood_type', $validated)) {
            $patient->blood_type = $validated['blood_type'];
        }
        if (array_key_exists('allergies', $validated)) {
            $patient->allergies = $validated['allergies'];
        }
        if (array_key_exists('chronic_diseases', $validated)) {
            $patient->chronic_diseases = $validated['chronic_diseases'];
        }
        if (array_key_exists('medications', $validated)) {
            $patient->medications = $validated['medications'];
        }
        if (array_key_exists('emergency_contacts', $validated)) {
            $patient->emergency_contacts = $validated['emergency_contacts'];
        }
        $patient->save();

        $user->profile_completed = true;
        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully.',
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
}
