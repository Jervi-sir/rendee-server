<?php

namespace App\Http\Controllers\Api\Patient\M1;

use App\Http\Controllers\Controller;
use App\Models\Center;
use App\Models\Doctor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class M1PatientController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $banner = [
            'title' => 'قريب مني',
            'text' => 'اعثر على الجهات الأقرب باستخدام موقعك الحالي.',
        ];

        $quickActions = [
            ['key' => 'doctors', 'label' => 'أطباء', 'target' => 'doctors'],
            ['key' => 'radiology', 'label' => 'أشعة', 'target' => 'radiology'],
            ['key' => 'labs', 'label' => 'مختبرات', 'target' => 'labs'],
            ['key' => 'nearby', 'label' => 'قريب مني', 'target' => 'patient_location'],
        ];

        $suggestions = [];

        // Fetch doctors
        $doctors = Doctor::with(['user', 'specialty'])
            ->where('is_available', true)
            ->limit(5)
            ->get();

        foreach ($doctors as $doctor) {
            $suggestions[] = [
                'entity_type' => 'doctor',
                'entity_id' => $doctor->id,
                'title' => 'د. ' . ($doctor->user->full_name ?? $doctor->user->name ?? ''),
                'subtitle' => $doctor->specialty?->ar ?? $doctor->specialty?->en ?? 'طبيب عام',
                'action_label' => 'احجز الآن',
                'primary' => count($suggestions) % 2 === 0,
            ];
        }

        // Fetch centers
        $centers = Center::with(['user', 'catalog'])
            ->where('is_active', true)
            ->limit(5)
            ->get();

        foreach ($centers as $center) {
            $suggestions[] = [
                'entity_type' => 'center',
                'entity_id' => $center->id,
                'title' => $center->name ?? $center->user->full_name ?? $center->user->name ?? '',
                'subtitle' => $center->catalog?->ar ?? $center->catalog?->en ?? 'مركز طبي',
                'action_label' => 'عرض التفاصيل',
                'primary' => count($suggestions) % 2 === 0,
            ];
        }

        return response()->json([
            'banner' => $banner,
            'quick_actions' => $quickActions,
            'suggestions' => $suggestions,
        ]);
    }
}
