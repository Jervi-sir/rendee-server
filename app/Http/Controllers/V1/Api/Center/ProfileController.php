<?php

namespace App\Http\Controllers\V1\Api\Center;

use App\Http\Controllers\Controller;
use App\Models\Center;
use App\Models\CenterCatalog;
use App\Models\CenterService;
use App\Models\CenterWorkingHour;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
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
                      !empty($center->wilaya_code) &&
                      $hasServices &&
                      $hasWorkingHours;

        if ($center->user) {
            $center->user->profile_complete = $isComplete;
            $center->user->save();
        }
    }

    /**
     * Retrieve the center profile.
     */
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
                    'address' => '',
                    'city' => '',
                    'latitude' => null,
                    'longitude' => null,
                    'wilaya_code' => null,
                ],
                'types' => $types,
            ]);
        }

        $user = $center->user;

        return response()->json([
            'profile' => [
                'id' => $center->id,
                'full_name' => $user->full_name ?? $user->name ?? '',
                'email' => $user->email ?? '',
                'phone' => $user->phone_number ?? '',
                'name' => $center->name ?? '',
                'type' => $center->center_catalog_code ?? 'clinic',
                'license_number' => $center->license_number ?? '',
                'phone_public' => $center->phone_public ?? '',
                'description' => $center->description ?? '',
                'emergency_24_7' => (bool) $center->emergency_24_7,
                'address' => $center->address ?? '',
                'city' => $center->city ?? '',
                'latitude' => $center->latitude ? (float) $center->latitude : null,
                'longitude' => $center->longitude ? (float) $center->longitude : null,
                'wilaya_code' => $center->wilaya_code,
            ],
            'types' => $types,
        ]);
    }

    /**
     * Update the center profile.
     */
    public function update(Request $request): JsonResponse
    {
        $center = $this->getCenter($request);
        if (!$center) {
            return response()->json(['error' => 'Center profile not found'], 404);
        }

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $center->user_id],
            'phone' => ['nullable', 'string', 'max:30'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'exists:center_catalogs,code'],
            'license_number' => ['nullable', 'string', 'max:100'],
            'phone_public' => ['nullable', 'string', 'max:30'],
            'description' => ['nullable', 'string'],
            'emergency_24_7' => ['required', 'boolean'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'wilaya_code' => ['nullable', 'string', 'exists:wilayas,code'],
        ]);

        // Update User account
        if ($center->user) {
            $center->user->update([
                'full_name' => $validated['full_name'],
                'email' => $validated['email'],
                'phone_number' => $validated['phone'],
            ]);
        }

        // Update Center profile
        $center->update([
            'name' => $validated['name'],
            'center_catalog_code' => $validated['type'],
            'license_number' => $validated['license_number'],
            'phone_public' => $validated['phone_public'],
            'description' => $validated['description'],
            'emergency_24_7' => $validated['emergency_24_7'],
            'address' => $validated['address'],
            'city' => $validated['city'],
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'wilaya_code' => $validated['wilaya_code'],
        ]);

        $this->updateProfileCompleteness($center);

        return response()->json([
            'success' => true,
            'profile_complete' => (bool) ($center->user->profile_complete ?? false),
            'center' => $center->load(['user', 'catalog']),
        ]);
    }
}
