<?php

namespace App\Http\Controllers\V1\Api\Partner;

use App\Http\Controllers\Controller;
use App\Models\Center;
use App\Models\CenterWorkingHour;
use App\Models\Professional;
use App\Models\ProfessionalSchedule;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
  /**
   * Display weekly working schedule for the authenticated partner (Professional or Center).
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

    $roleCode = $user->user_role_code;

    if ($roleCode === 'center' || $user->center) {
      $center = Center::where('user_id', $user->id)->first();
      if (! $center) {
        $center = Center::first();
      }

      if (! $center) {
        return response()->json(['success' => true, 'schedules' => $this->getDefaultWeeklyStructure([])]);
      }

      $existingHours = CenterWorkingHour::where('center_id', $center->id)
        ->get()
        ->keyBy('day_of_week');

      return response()->json([
        'success' => true,
        'schedules' => $this->getDefaultWeeklyStructure($existingHours),
      ]);
    }

    // Default Professional
    $professional = Professional::where('user_id', $user->id)->first();
    if (! $professional) {
      $professional = Professional::first();
    }

    if (! $professional) {
      return response()->json(['success' => true, 'schedules' => $this->getDefaultWeeklyStructure([])]);
    }

    $existingSchedules = ProfessionalSchedule::where('professional_id', $professional->id)
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
    ]);

    $roleCode = $user->user_role_code;

    if ($roleCode === 'center' || $user->center) {
      $center = Center::firstOrCreate(['user_id' => $user->id], ['name' => $user->full_name]);

      foreach ($validated['schedules'] as $item) {
        CenterWorkingHour::updateOrCreate(
          [
            'center_id' => $center->id,
            'day_of_week' => (int) $item['day_of_week'],
          ],
          [
            'start_time' => $item['start_time'] ?? '08:00',
            'end_time' => $item['end_time'] ?? '17:00',
            'is_active' => (bool) $item['is_active'],
          ]
        );
      }

      $fullSchedules = $this->getDefaultWeeklyStructure(
        CenterWorkingHour::where('center_id', $center->id)->get()->keyBy('day_of_week')
      );

      return response()->json([
        'success' => true,
        'message' => 'Weekly schedule updated successfully.',
        'schedules' => $fullSchedules,
      ]);
    }

    // Default Professional
    $professional = Professional::firstOrCreate(
      ['user_id' => $user->id],
      ['profession_code' => 'doctor']
    );

    foreach ($validated['schedules'] as $item) {
      ProfessionalSchedule::updateOrCreate(
        [
          'professional_id' => $professional->id,
          'day_of_week' => (int) $item['day_of_week'],
        ],
        [
          'start_time' => $item['start_time'] ?? '08:00',
          'end_time' => $item['end_time'] ?? '17:00',
          'is_active' => (bool) $item['is_active'],
        ]
      );
    }

    $fullSchedules = $this->getDefaultWeeklyStructure(
      ProfessionalSchedule::where('professional_id', $professional->id)->get()->keyBy('day_of_week')
    );

    return response()->json([
      'success' => true,
      'message' => 'Weekly schedule updated successfully.',
      'schedules' => $fullSchedules,
    ]);
  }

  /**
   * Generate 7-day weekly schedule structure (0=Sunday to 6=Saturday).
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

      $result[] = [
        'id' => $existing?->id,
        'day_of_week' => $day,
        'day_name' => $daysMap[$day]['ar'],
        'day_name_en' => $daysMap[$day]['en'],
        'day_name_fr' => $daysMap[$day]['fr'],
        'start_time' => $existing?->start_time ? substr($existing->start_time, 0, 5) : '08:00',
        'end_time' => $existing?->end_time ? substr($existing->end_time, 0, 5) : '17:00',
        'is_active' => $existing ? (bool) $existing->is_active : $defaultActive,
      ];
    }

    return $result;
  }
}
