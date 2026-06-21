<?php

namespace App\Http\Controllers\Api\Doctor\M4;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DoctorWeeklyScheduleController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $doctor = null;

        if ($user) {
            $doctor = Doctor::where('user_id', $user->id)->first();
        }

        if (!$doctor) {
            $doctor = Doctor::first();
        }

        if (!$doctor) {
            return response()->json([
                'schedule' => [],
            ]);
        }

        $schedules = DoctorSchedule::where('doctor_id', $doctor->id)->get();

        $scheduleData = [];
        foreach ($schedules as $s) {
            $scheduleData[] = [
                'id' => $s->id,
                'day_of_week' => (int)$s->day_of_week,
                'start_time' => $s->start_time ? substr($s->start_time, 0, 5) : null,
                'end_time' => $s->end_time ? substr($s->end_time, 0, 5) : null,
                'is_active' => (bool)$s->is_active,
            ];
        }

        return response()->json([
            'schedule' => $scheduleData,
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $user = $request->user();
        $doctor = null;

        if ($user) {
            $doctor = Doctor::where('user_id', $user->id)->first();
        }

        if (!$doctor) {
            $doctor = Doctor::first();
        }

        if (!$doctor) {
            return response()->json(['error' => 'Doctor profile not found'], 404);
        }

        $validated = $request->validate([
            'schedule' => 'required|array',
            'schedule.*.day_of_week' => 'required|integer|between:0,6',
            'schedule.*.start_time' => 'nullable|string',
            'schedule.*.end_time' => 'nullable|string',
            'schedule.*.is_active' => 'required|boolean',
        ]);

        foreach ($validated['schedule'] as $item) {
            $schedule = DoctorSchedule::updateOrCreate(
                [
                    'doctor_id' => $doctor->id,
                    'day_of_week' => $item['day_of_week'],
                ],
                [
                    'start_time' => $item['is_active'] && !empty($item['start_time']) ? $item['start_time'] . ':00' : null,
                    'end_time' => $item['is_active'] && !empty($item['end_time']) ? $item['end_time'] . ':00' : null,
                    'is_active' => $item['is_active'],
                ]
            );
        }

        // Recheck profile completeness
        $schedulesCount = DoctorSchedule::where('doctor_id', $doctor->id)->where('is_active', true)->count();
        $isComplete = !empty($doctor->speciality_code) &&
                      !empty($doctor->license_number) &&
                      !empty($doctor->years_experience) &&
                      !empty($doctor->bio) &&
                      !empty($doctor->address) &&
                      !empty($doctor->city) &&
                      !empty($doctor->phone_public) &&
                      $doctor->is_available &&
                      $schedulesCount > 0;

        if ($doctor->user) {
            $doctor->user->profile_complete = $isComplete;
            $doctor->user->save();
        }

        return response()->json([
            'success' => true,
            'profile_complete' => $isComplete,
        ]);
    }
}
