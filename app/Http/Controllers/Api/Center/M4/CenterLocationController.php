<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api\Center\M4;

use App\Http\Controllers\Controller;
use App\Models\Center;
use App\Models\CenterService;
use App\Models\CenterWorkingHour;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CenterLocationController extends Controller
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
                'profile' => [
                    'address' => '',
                    'city' => '',
                ],
            ]);
        }

        return response()->json([
            'profile' => [
                'address' => $center->address ?? '',
                'city' => $center->city ?? '',
            ],
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $center = $this->getCenter($request);
        if (!$center) {
            return response()->json(['error' => 'Center profile not found'], 404);
        }

        $validated = $request->validate([
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:255',
        ]);

        $center->update([
            'address' => $validated['address'],
            'city' => $validated['city'],
        ]);

        $this->updateProfileCompleteness($center);

        return response()->json([
            'success' => true,
            'profile_complete' => (bool)($center->user->profile_complete ?? false),
        ]);
    }
}
