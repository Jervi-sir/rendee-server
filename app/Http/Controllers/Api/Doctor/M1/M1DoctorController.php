<?php

namespace App\Http\Controllers\Api\Doctor\M1;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Doctor;
use App\Models\DoctorService;
use App\Models\Speciality;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class M1DoctorController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $doctor = null;
        $user = $request->user();

        if ($user) {
            $doctor = Doctor::with(['user', 'specialty'])->where('user_id', $user->id)->first();
        }

        if (!$doctor) {
            $doctor = Doctor::with(['user', 'specialty'])->first();
        }

        if (!$doctor) {
            return response()->json([
                'header' => [
                    'doctor_name' => 'د. طبيب تجريبي',
                    'speciality' => 'طب عام',
                    'date_label' => Carbon::now()->translatedFormat('l، d F Y'),
                ],
                'stats' => [
                    ['key' => 'today_bookings', 'label' => 'حجوزات اليوم', 'value' => '0'],
                    ['key' => 'unique_patients', 'label' => 'مرضى فريدون', 'value' => '0'],
                    ['key' => 'completed_bookings', 'label' => 'مكتملة', 'value' => '0'],
                ],
                'agenda' => [],
                'actions' => [],
            ]);
        }

        // Header Data
        $doctorName = 'د. ' . ($doctor->user->full_name ?? $doctor->user->name ?? 'طبيب');
        $speciality = $doctor->specialty?->ar ?? $doctor->specialty?->en ?? 'طبيب عام';
        $dateLabel = Carbon::now()->translatedFormat('l، d F Y');

        // Stats queries
        $today = Carbon::today()->format('Y-m-d');
        
        $todayBookingsCount = Booking::where('bookable_type', Doctor::class)
            ->where('bookable_id', $doctor->id)
            ->where('booking_date', $today)
            ->count();

        $uniquePatientsCount = Booking::where('bookable_type', Doctor::class)
            ->where('bookable_id', $doctor->id)
            ->distinct('patient_id')
            ->count('patient_id');

        $completedBookingsCount = Booking::where('bookable_type', Doctor::class)
            ->where('bookable_id', $doctor->id)
            ->where('status_code', 'completed')
            ->count();

        // Agenda Items
        $bookings = Booking::with(['doctorService.serviceCatalog', 'patient.user'])
            ->where('bookable_type', Doctor::class)
            ->where('bookable_id', $doctor->id)
            ->orderBy('booking_date', 'asc')
            ->orderBy('booking_time', 'asc')
            ->get();

        $agenda = [];
        foreach ($bookings as $booking) {
            $statusLabel = 'مؤكد';
            if ($booking->status_code === 'pending') {
                $statusLabel = 'قيد الانتظار';
            } elseif ($booking->status_code === 'cancelled') {
                $statusLabel = 'ملغي';
            } elseif ($booking->status_code === 'completed') {
                $statusLabel = 'مكتمل';
            }

            $agenda[] = [
                'id' => $booking->id,
                'time' => Carbon::parse($booking->booking_time)->format('H:i'),
                'date' => Carbon::parse($booking->booking_date)->format('Y-m-d'),
                'patient_name' => $booking->patient_name ?? $booking->patient?->user?->full_name ?? 'مريض',
                'visit_type' => $booking->doctorService?->serviceCatalog?->ar ?? $booking->doctorService?->serviceCatalog?->en ?? 'استشارة',
                'status' => $statusLabel,
                'active' => in_array($booking->status_code, ['confirmed', 'pending']),
            ];
        }

        return response()->json([
            'header' => [
                'doctor_name' => $doctorName,
                'speciality' => $speciality,
                'date_label' => $dateLabel,
            ],
            'stats' => [
                ['key' => 'today_bookings', 'label' => 'حجوزات اليوم', 'value' => (string)$todayBookingsCount],
                ['key' => 'unique_patients', 'label' => 'مرضى فريدون', 'value' => (string)$uniquePatientsCount],
                ['key' => 'completed_bookings', 'label' => 'مكتملة', 'value' => (string)$completedBookingsCount],
            ],
            'agenda' => $agenda,
            'actions' => [],
        ]);
    }
}
