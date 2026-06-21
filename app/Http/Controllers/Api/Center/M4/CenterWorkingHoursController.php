<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api\Center\M4;

use App\Http\Controllers\Controller;
use App\Models\Center;
use App\Models\CenterService;
use App\Models\CenterWorkingHour;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CenterWorkingHoursController extends Controller
{
    private function getCenter(Request $request): ?Center
    {
        $user = $request->user();
        $center = null;
        if ($user) {
            $center = Center::where('user_id', $user->id)->first();
        }
        if (!$center) {
            $center = Center::first();
        }
        return $center;
    }

    private function updateProfileCompleteness(Center $center): void
    {
        $hasServices = CenterService::where('center_id', $center->id)->where('is_active', true)->exists();
        $hasWorkingHours = CenterWorkingHour::where('center_id', $center->id)->where('is_available', true)->exists();

        $isComplete = !empty($center->license_number) &&
                      !empty($center->description) &&
                      !empty($center->address) &&
                      !empty($center->city) &&
                      $hasServices &&
                      $hasWorkingHours;

        if ($center->user) {
            $center->user->profile_complete = $isComplete;
            $center->user->save();
        }
    }

    public function show(Request $request): JsonResponse
    {
        $center = $this->getCenter($request);

        if (!$center) {
            return response()->json([
                'schedule' => [],
            ]);
        }

        $schedules = CenterWorkingHour::where('center_id', $center->id)
            ->orderBy('slot_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();

        $scheduleData = [];
        foreach ($schedules as $s) {
            $scheduleData[] = [
                'id' => $s->id,
                'slot_date' => $s->slot_date ? Carbon::parse($s->slot_date)->format('Y-m-d') : '',
                'start_time' => $s->start_time ? substr($s->start_time, 0, 5) : '',
                'end_time' => $s->end_time ? substr($s->end_time, 0, 5) : '',
                'is_available' => (bool)$s->is_available,
            ];
        }

        return response()->json([
            'schedule' => $scheduleData,
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $center = $this->getCenter($request);
        if (!$center) {
            return response()->json(['error' => 'Center profile not found'], 404);
        }

        $validated = $request->validate([
            'schedule' => 'required|array',
            'schedule.*.slot_date' => 'required|date_format:Y-m-d',
            'schedule.*.start_time' => 'required|string',
            'schedule.*.end_time' => 'required|string',
            'schedule.*.is_available' => 'required|boolean',
        ]);

        // Delete existing and recreate
        CenterWorkingHour::where('center_id', $center->id)->delete();

        foreach ($validated['schedule'] as $item) {
            $startTime = strlen($item['start_time']) === 5 ? $item['start_time'] . ':00' : $item['start_time'];
            $endTime = strlen($item['end_time']) === 5 ? $item['end_time'] . ':00' : $item['end_time'];

            CenterWorkingHour::create([
                'center_id' => $center->id,
                'slot_date' => $item['slot_date'],
                'start_time' => $startTime,
                'end_time' => $endTime,
                'is_available' => $item['is_available'],
            ]);
        }

        $this->updateProfileCompleteness($center);

        return response()->json([
            'success' => true,
            'profile_complete' => (bool)($center->user->profile_complete ?? false),
        ]);
    }
}
