<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Center\M1;

use App\Http\Controllers\Controller;
use App\Models\Center;
use App\Models\Booking;
use App\Models\CenterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class M1CenterController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $center = null;

        if ($user) {
            $center = Center::where('user_id', $user->id)->first();
        }

        if (!$center) {
            $center = Center::first();
        }

        if (!$center) {
            return response()->json([
                'header' => [
                    'center_name' => 'المركز الطبي',
                    'status_label' => 'لا توجد بيانات',
                ],
                'stats' => [
                    ['key' => 'today_bookings', 'label' => 'حجوزات اليوم', 'value' => '0'],
                    ['key' => 'estimated_revenue', 'label' => 'الإيرادات المتوقعة', 'value' => '0 ر.س'],
                ],
                'services' => [],
                'pending_bookings' => [
                    'count' => 0,
                    'title' => 'طلبات الحجز الجديدة',
                    'subtitle' => 'لا توجد طلبات جديدة حالياً',
                ],
            ]);
        }

        $today = Carbon::today()->format('Y-m-d');

        // Bookings count for today
        $todayBookingsCount = Booking::where('bookable_type', Center::class)
            ->where('bookable_id', $center->id)
            ->where('booking_date', $today)
            ->where('status_code', '!=', 'cancelled')
            ->count();

        // Estimated revenue calculation
        $todayBookings = Booking::where('bookable_type', Center::class)
            ->where('bookable_id', $center->id)
            ->where('booking_date', $today)
            ->whereIn('status_code', ['confirmed', 'completed'])
            ->with('centerService')
            ->get();

        $revenue = 0.00;
        foreach ($todayBookings as $b) {
            if ($b->centerService) {
                $revenue += (float)$b->centerService->price;
            }
        }

        // Active services
        $activeServices = CenterService::where('center_id', $center->id)
            ->where('is_active', true)
            ->with('serviceCatalog')
            ->get();

        $servicesData = [];
        foreach ($activeServices as $s) {
            $servicesData[] = [
                'id' => $s->id,
                'name' => $s->serviceCatalog?->ar ?? $s->serviceCatalog?->en ?? 'خدمة طبية',
                'price' => number_format((float)$s->price, 0) . '  دج',
                'type' => $s->serviceCatalog?->ar ?? 'تحاليل/أشعة',
            ];
        }

        // Pending bookings
        $pendingCount = Booking::where('bookable_type', Center::class)
            ->where('bookable_id', $center->id)
            ->where('status_code', 'pending')
            ->count();

        $pendingTitle = 'طلبات الحجز الجديدة';
        $pendingSubtitle = $pendingCount > 0
            ? "{$pendingCount} طلبات بحاجة إلى مراجعة"
            : 'لا توجد طلبات جديدة حالياً';

        return response()->json([
            'header' => [
                'center_name' => $center->name ?? 'المركز الطبي',
                'status_label' => $center->is_active ? 'مفتوح - جميع الخدمات متاحة' : 'مغلق مؤقتاً',
            ],
            'stats' => [
                [
                    'key' => 'today_bookings',
                    'label' => 'حجوزات اليوم',
                    'value' => (string)$todayBookingsCount,
                ],
                [
                    'key' => 'estimated_revenue',
                    'label' => 'الإيرادات المتوقعة',
                    'value' => number_format($revenue, 0) . '  دج',
                ],
            ],
            'services' => $servicesData,
            'pending_bookings' => [
                'count' => $pendingCount,
                'title' => $pendingTitle,
                'subtitle' => $pendingSubtitle,
            ],
        ]);
    }
}
