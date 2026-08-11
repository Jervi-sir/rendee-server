<?php

namespace App\Http\Controllers\V1\Api\Partner;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Professional;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $professional = null;

        if ($user) {
            $professional = Professional::with(['user', 'specialty'])->where('user_id', $user->id)->first();
        }

        if (! $professional) {
            $professional = Professional::with(['user', 'specialty'])->first();
        }

        $page = max(1, (int) $request->query('page', 1));
        $perPage = max(1, min(100, (int) $request->query('per_page', 10)));

        if (! $professional) {
            return response()->json([
                'header' => [
                    'professional_name' => 'أخصائي تجريبي',
                    'speciality' => 'عام',
                    'date_label' => Carbon::now()->translatedFormat('l، d F Y'),
                ],
                'stats' => [
                    'today_appointments' => 0,
                    'completed_today' => 0,
                    'total_completed' => 0,
                    'pending_requests' => 0,
                ],
                'stats_list' => [
                    ['key' => 'today_appointments', 'label' => 'حجوزات اليوم', 'value' => '0'],
                    ['key' => 'completed_today', 'label' => 'المكتملة اليوم', 'value' => '0'],
                    ['key' => 'total_completed', 'label' => 'إجمالي المكتملة', 'value' => '0'],
                    ['key' => 'pending_requests', 'label' => 'قيد الانتظار', 'value' => '0'],
                ],
                'appointments' => [],
                'current_page' => 1,
                'next_page' => null,
                'total' => 0,
                'agenda' => [],
            ]);
        }

        // Header details
        $prefix = $professional->profession_code === 'doctor' ? 'د. ' : '';
        $professionalName = $prefix . ($professional->user->full_name ?? $professional->user->name ?? 'أخصائي');
        $speciality = $professional->specialty?->ar ?? $professional->specialty?->en ?? 'عام';
        $dateLabel = Carbon::now()->translatedFormat('l، d F Y');

        // Today's Stats
        $today = Carbon::today()->format('Y-m-d');

        $todayBookingsCount = Booking::where('bookable_type', Professional::class)
            ->where('bookable_id', $professional->id)
            ->where('booking_date', $today)
            ->count();

        $completedTodayCount = Booking::where('bookable_type', Professional::class)
            ->where('bookable_id', $professional->id)
            ->where('booking_date', $today)
            ->where('status_code', 'completed')
            ->count();

        $completedBookingsCount = Booking::where('bookable_type', Professional::class)
            ->where('bookable_id', $professional->id)
            ->where('status_code', 'completed')
            ->count();

        $pendingBookingsCount = Booking::where('bookable_type', Professional::class)
            ->where('bookable_id', $professional->id)
            ->where('status_code', 'pending')
            ->count();

        // Paginated Newest Appointments
        $paginator = Booking::with(['service.serviceCatalog', 'patient.user', 'status'])
            ->where('bookable_type', Professional::class)
            ->where('bookable_id', $professional->id)
            ->orderBy('created_at', 'desc')
            ->orderBy('booking_date', 'desc')
            ->orderBy('booking_time', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        $appointments = collect($paginator->items())->map(function ($booking) {
            $statusLabel = 'مؤكد';
            if ($booking->status_code === 'pending') {
                $statusLabel = 'قيد الانتظار';
            } elseif ($booking->status_code === 'cancelled') {
                $statusLabel = 'ملغي';
            } elseif ($booking->status_code === 'completed') {
                $statusLabel = 'مكتمل';
            }

            $serviceName = $booking->service?->serviceCatalog?->ar
                ?? $booking->service?->serviceCatalog?->en
                ?? $booking->service?->name
                ?? 'استشارة';

            return [
                'id' => $booking->id,
                'reference' => $booking->reference,
                'patient_name' => $booking->patient_name ?? $booking->patient?->user?->full_name ?? $booking->patient?->user?->name ?? 'مريض',
                'patient_phone' => $booking->patient_phone ?? $booking->patient?->user?->phone_number ?? '',
                'visit_type' => $serviceName,
                'service_name' => $serviceName,
                'date' => $booking->booking_date ? (is_string($booking->booking_date) ? $booking->booking_date : $booking->booking_date->format('Y-m-d')) : '',
                'time' => $booking->booking_time ? Carbon::parse($booking->booking_time)->format('H:i') : '',
                'status_code' => $booking->status_code,
                'status' => $statusLabel,
                'notes' => $booking->notes,
                'created_at' => $booking->created_at?->toIso8601String(),
                'active' => in_array($booking->status_code, ['confirmed', 'pending']),
            ];
        });

        return response()->json([
            'header' => [
                'professional_name' => $professionalName,
                'speciality' => $speciality,
                'date_label' => $dateLabel,
            ],
            'stats' => [
                'today_appointments' => $todayBookingsCount,
                'completed_today' => $completedTodayCount,
                'total_completed' => $completedBookingsCount,
                'pending_requests' => $pendingBookingsCount,
            ],
            'stats_list' => [
                ['key' => 'today_appointments', 'label' => 'حجوزات اليوم', 'value' => (string) $todayBookingsCount],
                ['key' => 'completed_today', 'label' => 'المكتملة اليوم', 'value' => (string) $completedTodayCount],
                ['key' => 'total_completed', 'label' => 'إجمالي المكتملة', 'value' => (string) $completedBookingsCount],
                ['key' => 'pending_requests', 'label' => 'قيد الانتظار', 'value' => (string) $pendingBookingsCount],
            ],
            'appointments' => $appointments,
            'current_page' => $paginator->currentPage(),
            'next_page' => $paginator->hasMorePages() ? $paginator->currentPage() + 1 : null,
            'total' => $paginator->total(),
            'agenda' => $appointments,
        ]);
    }
}
