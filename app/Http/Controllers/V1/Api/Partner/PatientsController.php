<?php

namespace App\Http\Controllers\V1\Api\Partner;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Partner;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PatientsController extends Controller
{
    /**
     * Get list of unique patients for the partner.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $partner = null;

        if ($user) {
            $partner = Partner::where('user_id', $user->id)->first();
        }

        if (! $partner) {
            $partner = Partner::first();
        }

        if (! $partner) {
            return response()->json([
                'patients' => [],
                'total' => 0,
            ]);
        }

        $search = $request->query('search');

        // Fetch distinct patient IDs from bookings for this partner
        $patientIds = Booking::where('partner_id', $partner->id)
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

        $patientsList = $query->get()->map(function ($patient) use ($partner) {
            $lastBooking = Booking::where('partner_id', $partner->id)
                ->where('patient_id', $patient->id)
                ->orderBy('booking_date', 'desc')
                ->first();

            $totalVisits = Booking::where('partner_id', $partner->id)
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
        $partner = null;
        if ($user) {
            $partner = Partner::where('user_id', $user->id)->first();
        }
        if (! $partner) {
            $partner = Partner::first();
        }

        $lastBooking = null;
        $totalVisits = 0;

        if ($partner) {
            $lastBooking = Booking::where('partner_id', $partner->id)
                ->where('patient_id', $patient->id)
                ->orderBy('booking_date', 'desc')
                ->first();

            $totalVisits = Booking::where('partner_id', $partner->id)
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

    /**
     * Get patient booking history / past visits.
     */
    public function history(Request $request, int $id): JsonResponse
    {
        $patient = Patient::find($id);

        if (! $patient) {
            return response()->json(['error' => 'Patient not found'], 404);
        }

        $user = $request->user();
        $partner = null;
        if ($user) {
            $partner = Partner::where('user_id', $user->id)->first();
        }
        if (! $partner) {
            $partner = Partner::first();
        }

        $query = Booking::with(['service.catalog', 'status'])
            ->where('patient_id', $patient->id);

        if ($partner) {
            $query->where('partner_id', $partner->id);
        }

        $history = $query->orderBy('booking_date', 'desc')
            ->orderBy('booking_time', 'desc')
            ->get()
            ->map(function ($booking) {
                $statusLabel = 'مؤكد';
                if ($booking->status_code === 'pending') {
                    $statusLabel = 'قيد الانتظار';
                } elseif ($booking->status_code === 'cancelled') {
                    $statusLabel = 'ملغي';
                } elseif ($booking->status_code === 'completed') {
                    $statusLabel = 'مكتمل';
                }

                $serviceName = $booking->service?->catalog?->ar
                    ?? $booking->service?->catalog?->en
                    ?? $booking->service?->name
                    ?? 'فحص طبي';

                return [
                    'id' => $booking->id,
                    'reference' => $booking->reference,
                    'date' => $booking->booking_date ? (is_string($booking->booking_date) ? $booking->booking_date : $booking->booking_date->format('Y-m-d')) : '',
                    'time' => $booking->booking_time ? \Carbon\Carbon::parse($booking->booking_time)->format('H:i') : '',
                    'doctor_name' => 'د. ' . ($booking->bookable?->name ?? 'طبيب'),
                    'specialty' => $booking->bookable?->specialty?->ar ?? 'عام',
                    'service_name' => $serviceName,
                    'visit_type' => $serviceName,
                    'status' => $booking->status_code,
                    'status_label' => $statusLabel,
                    'notes' => $booking->notes ?? 'لا توجد ملاحظات إضافية',
                ];
            });

        return response()->json([
            'success' => true,
            'history' => $history,
            'total' => count($history),
        ]);
    }
}
