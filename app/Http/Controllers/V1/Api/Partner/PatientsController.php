<?php

namespace App\Http\Controllers\V1\Api\Partner;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Patient;
use App\Models\Professional;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PatientsController extends Controller
{
    /**
     * Get list of unique patients for the professional.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $professional = null;

        if ($user) {
            $professional = Professional::where('user_id', $user->id)->first();
        }

        if (! $professional) {
            $professional = Professional::first();
        }

        if (! $professional) {
            return response()->json([
                'patients' => [],
                'total' => 0,
            ]);
        }

        $search = $request->query('search');

        // Fetch distinct patient IDs from bookings for this professional
        $patientIds = Booking::where('bookable_type', Professional::class)
            ->where('bookable_id', $professional->id)
            ->whereNotNull('patient_id')
            ->distinct()
            ->pluck('patient_id');

        $query = Patient::with(['user'])
            ->whereIn('id', $patientIds);

        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $patientsList = $query->get()->map(function ($patient) use ($professional) {
            $lastBooking = Booking::where('bookable_type', Professional::class)
                ->where('bookable_id', $professional->id)
                ->where('patient_id', $patient->id)
                ->orderBy('booking_date', 'desc')
                ->first();

            $totalVisits = Booking::where('bookable_type', Professional::class)
                ->where('bookable_id', $professional->id)
                ->where('patient_id', $patient->id)
                ->count();

            return [
                'id' => $patient->id,
                'name' => $patient->user?->full_name ?? $patient->user?->name ?? 'مريض',
                'phone' => $patient->user?->phone_number ?? '',
                'email' => $patient->user?->email ?? '',
                'birth_date' => $patient->date_of_birth ? (is_string($patient->date_of_birth) ? $patient->date_of_birth : $patient->date_of_birth->format('Y-m-d')) : null,
                'gender' => $patient->gender === 'male' ? 'ذكر' : ($patient->gender === 'female' ? 'أنثى' : $patient->gender),
                'blood_type' => $patient->blood_type ?? '',
                'city' => $patient->city ?? 'الجزائر العاصمة',
                'address' => $patient->address ?? '',
                'emergency_phone' => is_array($patient->emergency_contacts) ? implode(', ', $patient->emergency_contacts) : ($patient->emergency_contacts ?? ''),
                'allergies' => is_array($patient->allergies) ? implode(', ', $patient->allergies) : ($patient->allergies ?? ''),
                'chronic_diseases' => is_array($patient->chronic_diseases) ? implode(', ', $patient->chronic_diseases) : ($patient->chronic_diseases ?? ''),
                'medications' => is_array($patient->medications) ? implode(', ', $patient->medications) : ($patient->medications ?? ''),
                'notes' => $patient->medical_notes ?? '',
                'total_visits' => $totalVisits,
                'last_visit' => $lastBooking ? (is_string($lastBooking->booking_date) ? $lastBooking->booking_date : $lastBooking->booking_date->format('Y-m-d')) : null,
            ];
        });

        return response()->json([
            'patients' => $patientsList,
            'total' => count($patientsList),
        ]);
    }

    /**
     * Get single patient details.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $patient = Patient::with(['user'])->find($id);

        if (! $patient) {
            return response()->json(['error' => 'Patient not found'], 404);
        }

        $user = $request->user();
        $professional = null;
        if ($user) {
            $professional = Professional::where('user_id', $user->id)->first();
        }
        if (! $professional) {
            $professional = Professional::first();
        }

        $lastBooking = null;
        $totalVisits = 0;

        if ($professional) {
            $lastBooking = Booking::where('bookable_type', Professional::class)
                ->where('bookable_id', $professional->id)
                ->where('patient_id', $patient->id)
                ->orderBy('booking_date', 'desc')
                ->first();

            $totalVisits = Booking::where('bookable_type', Professional::class)
                ->where('bookable_id', $professional->id)
                ->where('patient_id', $patient->id)
                ->count();
        }

        return response()->json([
            'patient' => [
                'id' => $patient->id,
                'name' => $patient->user?->full_name ?? $patient->user?->name ?? 'مريض',
                'phone' => $patient->user?->phone_number ?? '',
                'email' => $patient->user?->email ?? '',
                'birth_date' => $patient->date_of_birth ? (is_string($patient->date_of_birth) ? $patient->date_of_birth : $patient->date_of_birth->format('Y-m-d')) : null,
                'gender' => $patient->gender === 'male' ? 'ذكر' : ($patient->gender === 'female' ? 'أنثى' : $patient->gender),
                'blood_type' => $patient->blood_type ?? '',
                'city' => $patient->city ?? 'الجزائر العاصمة',
                'address' => $patient->address ?? '',
                'emergency_phone' => is_array($patient->emergency_contacts) ? implode(', ', $patient->emergency_contacts) : ($patient->emergency_contacts ?? ''),
                'allergies' => is_array($patient->allergies) ? implode(', ', $patient->allergies) : ($patient->allergies ?? ''),
                'chronic_diseases' => is_array($patient->chronic_diseases) ? implode(', ', $patient->chronic_diseases) : ($patient->chronic_diseases ?? ''),
                'medications' => is_array($patient->medications) ? implode(', ', $patient->medications) : ($patient->medications ?? ''),
                'notes' => $patient->medical_notes ?? '',
                'total_visits' => $totalVisits,
                'last_visit' => $lastBooking ? (is_string($lastBooking->booking_date) ? $lastBooking->booking_date : $lastBooking->booking_date->format('Y-m-d')) : null,
            ],
        ]);
    }
}
