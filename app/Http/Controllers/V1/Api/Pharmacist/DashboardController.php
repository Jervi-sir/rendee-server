<?php

namespace App\Http\Controllers\V1\Api\Pharmacist;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $pharmacy = null;
        $user = $request->user();

        if ($user) {
            $pharmacy = Pharmacy::with(['user'])->where('user_id', $user->id)->first();
        }

        if (!$pharmacy) {
            $pharmacy = Pharmacy::with(['user'])->first();
        }

        if (!$pharmacy) {
            return response()->json([
                'header' => [
                    'pharmacy_name' => 'صيدلية تجريبية',
                    'location' => 'غير محدد',
                    'date_label' => Carbon::now()->translatedFormat('l، d F Y'),
                ],
                'stats' => [
                    ['key' => 'is_available', 'label' => 'حالة المناوبة', 'value' => 'غير نشط'],
                    ['key' => 'profile_status', 'label' => 'حالة الملف', 'value' => 'غير مكتمل'],
                ],
                'info' => [],
            ]);
        }

        $pharmacyName = $pharmacy->name ?? $pharmacy->user->full_name ?? $pharmacy->user->name ?? 'صيدلية';
        $location = $pharmacy->location ?? 'غير محدد';
        $dateLabel = Carbon::now()->translatedFormat('l، d F Y');

        return response()->json([
            'header' => [
                'pharmacy_name' => $pharmacyName,
                'location' => $location,
                'date_label' => $dateLabel,
            ],
            'stats' => [
                [
                    'key' => 'is_available',
                    'label' => 'حالة المناوبة/التوفر',
                    'value' => $pharmacy->is_available ? 'نشط (مفتوح)' : 'غير نشط (مغلق)',
                    'status' => (bool) $pharmacy->is_available
                ],
                [
                    'key' => 'profile_complete',
                    'label' => 'اكتمال الملف الشخصي',
                    'value' => ($pharmacy->user?->profile_complete ?? false) ? 'مكتمل' : 'غير مكتمل',
                    'status' => (bool) ($pharmacy->user?->profile_complete ?? false)
                ],
                [
                    'key' => 'coordinates',
                    'label' => 'الإحداثيات الجغرافية',
                    'value' => ($pharmacy->latitude && $pharmacy->longitude) ? 'محددة' : 'غير محددة',
                    'status' => (bool) ($pharmacy->latitude && $pharmacy->longitude)
                ]
            ],
            'info' => [
                'bio' => $pharmacy->bio ?? 'لم يتم تعيين وصف للصيدلية بعد.',
                'city' => $pharmacy->location ?? 'غير محدد',
                'wilaya_code' => $pharmacy->wilaya_code ?? 'غير محدد',
            ],
        ]);
    }
}
