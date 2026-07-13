<?php

namespace App\Http\Controllers\V1\Api\Professional;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Professional;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $professional = null;
        $user = $request->user();

        if ($user) {
            $professional = Professional::with(['user', 'specialty'])->where('user_id', $user->id)->first();
        }

        if (!$professional) {
            $professional = Professional::with(['user', 'specialty'])->first();
        }

        if (!$professional) {
            return response()->json([
                'header' => [
                    'professional_name' => 'أخصائي تجريبي',
                    'speciality' => 'عام',
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

        // Header details
        $prefix = $professional->profession_code === 'doctor' ? 'د. ' : '';
        $professionalName = $prefix . ($professional->user->full_name ?? $professional->user->name ?? 'أخصائي');
        $speciality = $professional->specialty?->ar ?? $professional->specialty?->en ?? 'عام';
        $dateLabel = Carbon::now()->translatedFormat('l، d F Y');

        // Stats queries
        $today = Carbon::today()->format('Y-m-d');
        
        $todayBookingsCount = Booking::where('bookable_type', Professional::class)
            ->where('bookable_id', $professional->id)
            ->where('booking_date', $today)
            ->count();

        $uniquePatientsCount = Booking::where('bookable_type', Professional::class)
            ->where('bookable_id', $professional->id)
            ->distinct('patient_id')
            ->count('patient_id');

        $completedBookingsCount = Booking::where('bookable_type', Professional::class)
            ->where('bookable_id', $professional->id)
            ->where('status_code', 'completed')
            ->count();

        // Agenda Items
        $bookings = Booking::with(['service.serviceCatalog', 'patient.user'])
            ->where('bookable_type', Professional::class)
            ->where('bookable_id', $professional->id)
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
                'visit_type' => $booking->service?->serviceCatalog?->ar ?? $booking->service?->serviceCatalog?->en ?? 'استشارة',
                'status' => $statusLabel,
                'active' => in_array($booking->status_code, ['confirmed', 'pending']),
            ];
        }

        return response()->json([
            'header' => [
                'professional_name' => $professionalName,
                'speciality' => $speciality,
                'date_label' => $dateLabel,
            ],
            'stats' => [
                ['key' => 'today_bookings', 'label' => 'حجوزات اليوم', 'value' => (string) $todayBookingsCount],
                ['key' => 'unique_patients', 'label' => 'مرضى فريدون', 'value' => (string) $uniquePatientsCount],
                ['key' => 'completed_bookings', 'label' => 'مكتملة', 'value' => (string) $completedBookingsCount],
            ],
            'agenda' => $agenda,
            'actions' => [],
        ]);
    }
}
