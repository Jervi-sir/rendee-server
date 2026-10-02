<?php

namespace App\Http\Controllers\V1\Api\Partner;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Partner;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $partner = null;

        if ($user) {
            $partner = Partner::with(['user', 'specialty', 'profession'])->where('user_id', $user->id)->first();
        }

        if (! $partner) {
            $partner = Partner::with(['user', 'specialty', 'profession'])->first();
        }

        $page = max(1, (int) $request->query('page', 1));
        $perPage = max(1, min(100, (int) $request->query('per_page', 10)));

        if (! $partner) {
            return response()->json([
                'header' => [
                    'partner_name' => 'شريك تجريبي',
                    'professional_name' => 'شريك تجريبي',
                    'speciality' => 'عام',
                    'date_label' => Carbon::now()->translatedFormat('l، d F Y'),
                ],
                'stats' => [
                    'pending' => 0,
                    'in_progress' => 0,
                    'confirmed' => 0,
                    'completed' => 0,
                    'cancelled' => 0,
                    'today_appointments' => 0,
                    'completed_today' => 0,
                    'total_completed' => 0,
                    'pending_requests' => 0,
                ],
                'stats_list' => [
                    ['key' => 'pending', 'label' => 'قيد الانتظار', 'value' => '0'],
                    ['key' => 'in_progress', 'label' => 'جاري الموعد', 'value' => '0'],
                    ['key' => 'confirmed', 'label' => 'مؤكدة', 'value' => '0'],
                    ['key' => 'completed', 'label' => 'المكتملة', 'value' => '0'],
                    ['key' => 'cancelled', 'label' => 'الملغاة', 'value' => '0'],
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
        $prefix = $partner->profession_code === 'doctor' ? '' : '';
        $partnerName = $prefix.($partner->name ?? $partner->user?->full_name ?? $partner->user?->name ?? 'شريك');
        $speciality = $partner->display_speciality ?? $partner->specialty?->ar ?? $partner->specialty?->en ?? 'عام';
        $dateLabel = Carbon::now()->translatedFormat('l، d F Y');

        // Today's Stats
        $today = Carbon::today()->format('Y-m-d');

        $todayBookingsCount = Booking::where('partner_id', $partner->id)
            ->where('booking_date', $today)
            ->count();

        $completedTodayCount = Booking::where('partner_id', $partner->id)
            ->where('booking_date', $today)
            ->where('status_code', Booking::STATUS_COMPLETED)
            ->count();

        $pendingBookingsCount = Booking::where('partner_id', $partner->id)
            ->where('status_code', Booking::STATUS_PENDING)
            ->count();

        $inProgressBookingsCount = Booking::where('partner_id', $partner->id)
            ->where('status_code', Booking::STATUS_IN_PROGRESS)
            ->count();

        $confirmedBookingsCount = Booking::where('partner_id', $partner->id)
            ->where('status_code', Booking::STATUS_CONFIRMED)
            ->count();

        $completedBookingsCount = Booking::where('partner_id', $partner->id)
            ->where('status_code', Booking::STATUS_COMPLETED)
            ->count();

        $cancelledBookingsCount = Booking::where('partner_id', $partner->id)
            ->where('status_code', Booking::STATUS_CANCELLED)
            ->count();

        // Paginated Newest Appointments
        $paginator = Booking::with(['service.catalog', 'patient.user', 'status'])
            ->where('partner_id', $partner->id)
            ->orderBy('booking_date', 'asc')
            ->orderBy('booking_time', 'asc')
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

            $serviceName = $booking->service?->catalog?->ar
                ?? $booking->service?->catalog?->en
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
                'partner_name' => $partnerName,
                'professional_name' => $partnerName,
                'speciality' => $speciality,
                'date_label' => $dateLabel,
            ],
            'stats' => [
                'pending' => $pendingBookingsCount,
                'in_progress' => $inProgressBookingsCount,
                'confirmed' => $confirmedBookingsCount,
                'completed' => $completedBookingsCount,
                'cancelled' => $cancelledBookingsCount,
                'today_appointments' => $todayBookingsCount,
                'completed_today' => $completedTodayCount,
                'total_completed' => $completedBookingsCount,
                'pending_requests' => $pendingBookingsCount,
            ],
            'stats_list' => [
                ['key' => 'pending', 'label' => 'قيد الانتظار', 'value' => (string) $pendingBookingsCount],
                ['key' => 'in_progress', 'label' => 'جاري الموعد', 'value' => (string) $inProgressBookingsCount],
                ['key' => 'confirmed', 'label' => 'مؤكدة', 'value' => (string) $confirmedBookingsCount],
                ['key' => 'completed', 'label' => 'المكتملة', 'value' => (string) $completedBookingsCount],
                ['key' => 'cancelled', 'label' => 'الملغاة', 'value' => (string) $cancelledBookingsCount],
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
