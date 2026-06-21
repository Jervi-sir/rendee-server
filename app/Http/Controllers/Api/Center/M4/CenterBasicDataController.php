<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api\Center\M4;

use App\Http\Controllers\Controller;
use App\Models\Center;
use App\Models\CenterCatalog;
use App\Models\CenterService;
use App\Models\CenterWorkingHour;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CenterBasicDataController extends Controller
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

        $types = CenterCatalog::all()->map(function ($item) {
            return [
                'value' => $item->code,
                'label' => $item->ar ?? $item->en ?? $item->code,
            ];
        });

        if (!$center) {
            return response()->json([
                'profile' => [
                    'full_name' => '',
                    'email' => '',
                    'phone' => '',
                    'name' => '',
                    'type' => 'clinic',
                    'license_number' => '',
                    'phone_public' => '',
                    'description' => '',
                    'emergency_24_7' => false,
                ],
                'types' => $types,
            ]);
        }

        $user = $center->user;

        return response()->json([
            'profile' => [
                'full_name' => $user->full_name ?? $user->name ?? '',
                'email' => $user->email ?? '',
                'phone' => $user->phone ?? '',
                'name' => $center->name ?? '',
                'type' => $center->center_catalog_code ?? 'clinic',
                'license_number' => $center->license_number ?? '',
                'phone_public' => $center->phone_public ?? '',
                'description' => $center->description ?? '',
                'emergency_24_7' => (bool)$center->emergency_24_7,
            ],
            'types' => $types,
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $center = $this->getCenter($request);
        if (!$center) {
            return response()->json(['error' => 'Center profile not found'], 404);
        }

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'license_number' => 'nullable|string|max:255',
            'phone_public' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'emergency_24_7' => 'required|boolean',
        ]);

        // Update User
        if ($center->user) {
            $center->user->update([
                'full_name' => $validated['full_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
            ]);
        }

        // Update Center
        $center->update([
            'name' => $validated['name'],
            'center_catalog_code' => $validated['type'],
            'license_number' => $validated['license_number'],
            'phone_public' => $validated['phone_public'],
            'description' => $validated['description'],
            'emergency_24_7' => $validated['emergency_24_7'],
        ]);

        $this->updateProfileCompleteness($center);

        return response()->json([
            'success' => true,
            'profile_complete' => (bool)($center->user->profile_complete ?? false),
        ]);
    }
}
