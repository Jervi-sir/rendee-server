<?php

namespace App\Http\Controllers\V1\Api\Partner;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Models\PartnerSchedule;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    /**
     * Display weekly working schedule for the authenticated partner.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            $user = User::first();
        }

        if (! $user) {
            return response()->json([
                'success' => true,
                'schedules' => $this->getDefaultWeeklyStructure([]),
            ]);
        }

        $partner = Partner::where('user_id', $user->id)->first();
        if (! $partner) {
            $partner = Partner::first();
        }

        if (! $partner) {
            return response()->json(['success' => true, 'schedules' => $this->getDefaultWeeklyStructure([])]);
        }

        $existingSchedules = PartnerSchedule::where('partner_id', $partner->id)
            ->get()
            ->keyBy('day_of_week');

        return response()->json([
            'success' => true,
            'schedules' => $this->getDefaultWeeklyStructure($existingSchedules),
        ]);
    }

    /**
     * Upsert (bulk update or create) the weekly working schedule for the partner.
     */
    public function upsert(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            $user = User::first();
        }

        if (! $user) {
            return response()->json(['error' => 'Partner profile not found'], 404);
        }

        $validated = $request->validate([
            'schedules' => ['required', 'array'],
            'schedules.*.day_of_week' => ['required', 'integer', 'between:0,6'],
            'schedules.*.start_time' => ['nullable', 'string'],
            'schedules.*.end_time' => ['nullable', 'string'],
            'schedules.*.is_active' => ['required', 'boolean'],
            'schedules.*.morning_start_time' => ['nullable', 'string'],
            'schedules.*.morning_end_time' => ['nullable', 'string'],
            'schedules.*.morning_is_active' => ['nullable', 'boolean'],
            'schedules.*.evening_start_time' => ['nullable', 'string'],
            'schedules.*.evening_end_time' => ['nullable', 'string'],
            'schedules.*.evening_is_active' => ['nullable', 'boolean'],
        ]);

        $partnerType = match ($user->user_role_code) {
            'center' => 'CENTER',
            'pharmacist' => 'PHARM',
            default => 'PRO',
        };

        $partner = Partner::firstOrCreate(
            ['user_id' => $user->id],
            [
                'partner_type' => $partnerType,
                'name' => $user->full_name,
            ]
        );

        foreach ($validated['schedules'] as $item) {
            $mStart = ! empty($item['morning_start_time']) ? $item['morning_start_time'] : '08:00';
            $mEnd = ! empty($item['morning_end_time']) ? $item['morning_end_time'] : '12:00';
            $mActive = isset($item['morning_is_active']) ? (bool) $item['morning_is_active'] : true;

            $eStart = ! empty($item['evening_start_time']) ? $item['evening_start_time'] : '13:30';
            $eEnd = ! empty($item['evening_end_time']) ? $item['evening_end_time'] : '17:00';
            $eActive = isset($item['evening_is_active']) ? (bool) $item['evening_is_active'] : true;

            // Overall start_time and end_time fallback for backwards compatibility
            $startTime = ! empty($item['start_time']) ? $item['start_time'] : ($mActive ? $mStart : $eStart);
            $endTime = ! empty($item['end_time']) ? $item['end_time'] : ($eActive ? $eEnd : $mEnd);

            PartnerSchedule::updateOrCreate(
                [
                    'partner_id' => $partner->id,
                    'day_of_week' => (int) $item['day_of_week'],
                ],
                [
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'is_active' => (bool) $item['is_active'],
                    'morning_start_time' => $mStart,
                    'morning_end_time' => $mEnd,
                    'morning_is_active' => $mActive,
                    'evening_start_time' => $eStart,
                    'evening_end_time' => $eEnd,
                    'evening_is_active' => $eActive,
                ]
            );
        }

        $fullSchedules = $this->getDefaultWeeklyStructure(
            PartnerSchedule::where('partner_id', $partner->id)->get()->keyBy('day_of_week')
        );

        return response()->json([
            'success' => true,
            'message' => 'Weekly schedule updated successfully.',
            'schedules' => $fullSchedules,
        ]);
    }

    /**
     * Generate 7-day weekly schedule structure (0=Sunday to 6=Saturday) with morning & evening shifts.
     */
    private function getDefaultWeeklyStructure($existingSchedules): array
    {
        $daysMap = [
            0 => ['en' => 'Sunday', 'ar' => 'الأحد', 'fr' => 'Dimanche'],
            1 => ['en' => 'Monday', 'ar' => 'الإثنين', 'fr' => 'Lundi'],
            2 => ['en' => 'Tuesday', 'ar' => 'الثلاثاء', 'fr' => 'Mardi'],
            3 => ['en' => 'Wednesday', 'ar' => 'الأربعاء', 'fr' => 'Mercredi'],
            4 => ['en' => 'Thursday', 'ar' => 'الخميس', 'fr' => 'Jeudi'],
            5 => ['en' => 'Friday', 'ar' => 'الجمعة', 'fr' => 'Vendredi'],
            6 => ['en' => 'Saturday', 'ar' => 'السبت', 'fr' => 'Samedi'],
        ];

        $result = [];

        for ($day = 0; $day <= 6; $day++) {
            $existing = $existingSchedules[$day] ?? null;
            $defaultActive = ($day >= 0 && $day <= 4);

            $mStart = $existing?->morning_start_time ? substr($existing->morning_start_time, 0, 5) : '08:00';
            $mEnd = $existing?->morning_end_time ? substr($existing->morning_end_time, 0, 5) : '12:00';
            $mActive = $existing ? (bool) ($existing->morning_is_active ?? true) : true;

            $eStart = $existing?->evening_start_time ? substr($existing->evening_start_time, 0, 5) : '13:30';
            $eEnd = $existing?->evening_end_time ? substr($existing->evening_end_time, 0, 5) : '17:00';
            $eActive = $existing ? (bool) ($existing->evening_is_active ?? true) : true;

            $result[] = [
                'id' => $existing?->id,
                'day_of_week' => $day,
                'day_name' => $daysMap[$day]['ar'],
                'day_name_en' => $daysMap[$day]['en'],
                'day_name_fr' => $daysMap[$day]['fr'],
                'start_time' => $existing?->start_time ? substr($existing->start_time, 0, 5) : '08:00',
                'end_time' => $existing?->end_time ? substr($existing->end_time, 0, 5) : '17:00',
                'is_active' => $existing ? (bool) $existing->is_active : $defaultActive,

                // Split shifts
                'morning_start_time' => $mStart,
                'morning_end_time' => $mEnd,
                'morning_is_active' => $mActive,

                'evening_start_time' => $eStart,
                'evening_end_time' => $eEnd,
                'evening_is_active' => $eActive,
            ];
        }

        return $result;
    }
}
