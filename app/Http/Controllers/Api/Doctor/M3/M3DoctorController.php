<?php

namespace App\Http\Controllers\Api\Doctor\M3;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class M3DoctorController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $doctor = null;
        $user = $request->user();

        if ($user) {
            $doctor = Doctor::where('user_id', $user->id)->first();
        }

        if (!$doctor) {
            $doctor = Doctor::first();
        }

        if (!$doctor) {
            return response()->json([
                'patients' => [],
            ]);
        }

        // Get patients who have booked with this doctor
        $query = Patient::with(['user'])
            ->whereHas('bookings', function ($q) use ($doctor) {
                $q->where('bookable_type', Doctor::class)
                  ->where('bookable_id', $doctor->id);
            });

        // Search query filter
        if ($request->filled('query')) {
            $search = $request->query('query');
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($uq) use ($search) {
                    $uq->where('full_name', 'like', "%{$search}%")
                       ->orWhere('name', 'like', "%{$search}%")
                       ->orWhere('phone_number', 'like', "%{$search}%");
                });
            });
        }

        $patients = $query->get();

        $patientItems = [];
        foreach ($patients as $patient) {
            $patientBookings = Booking::where('patient_id', $patient->id)
                ->where('bookable_type', Doctor::class)
                ->where('bookable_id', $doctor->id)
                ->orderBy('booking_date', 'desc')
                ->get();

            $visitsCount = $patientBookings->count();
            $lastBooking = $patientBookings->first();
            $lastVisit = $lastBooking ? Carbon::parse($lastBooking->booking_date)->format('Y-m-d') : null;

            $patientItems[] = [
                'id' => $patient->id,
                'name' => $patient->user->full_name ?? $patient->user->name ?? 'مريض',
                'phone' => $patient->user->phone_number ?? $patient->user->phone ?? 'رقم غير متوفر',
                'last_visit' => $lastVisit,
                'visits_count' => $visitsCount,
                'medical_records_count' => max(1, (int)($visitsCount * 1.5)),
            ];
        }

        return response()->json([
            'patients' => $patientItems,
        ]);
    }
}
