<?php

namespace App\Http\Controllers\V1\Api\Pharmacist;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Retrieve the pharmacy profile.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $pharmacy = null;

        if ($user) {
            $pharmacy = Pharmacy::with(['user'])->where('user_id', $user->id)->first();
        }

        if (!$pharmacy) {
            $pharmacy = Pharmacy::with(['user'])->first();
        }

        if (!$pharmacy) {
            return response()->json([
                'profile' => [
                    'name' => '',
                    'full_name' => '',
                    'email' => '',
                    'phone' => '',
                    'location' => '',
                    'bio' => '',
                    'latitude' => null,
                    'longitude' => null,
                    'is_available' => false,
                    'wilaya_code' => null,
                ]
            ]);
        }

        return response()->json([
            'profile' => [
                'id' => $pharmacy->id,
                'name' => $pharmacy->name ?? '',
                'full_name' => $pharmacy->user->full_name ?? $pharmacy->user->name ?? '',
                'email' => $pharmacy->user->email ?? '',
                'phone' => $pharmacy->user->phone_number ?? '',
                'location' => $pharmacy->location,
                'bio' => $pharmacy->bio,
                'latitude' => $pharmacy->latitude ? (float) $pharmacy->latitude : null,
                'longitude' => $pharmacy->longitude ? (float) $pharmacy->longitude : null,
                'is_available' => (bool) $pharmacy->is_available,
                'wilaya_code' => $pharmacy->wilaya_code,
            ]
        ]);
    }

    /**
     * Update the pharmacy profile.
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();
        $pharmacy = null;

        if ($user) {
            $pharmacy = Pharmacy::where('user_id', $user->id)->first();
        }

        if (!$pharmacy) {
            $pharmacy = Pharmacy::first();
            if ($pharmacy) {
                $user = User::find($pharmacy->user_id);
            }
        }

        if (!$pharmacy || !$user) {
            return response()->json(['error' => 'Pharmacy profile not found'], 404);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:30'],
            'location' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'is_available' => ['required', 'boolean'],
            'wilaya_code' => ['nullable', 'string', 'exists:wilayas,code'],
        ]);

        // Update User account details
        $user->full_name = $validated['full_name'];
        $user->email = $validated['email'];
        $user->phone_number = $validated['phone'];
        $user->save();

        // Update Pharmacy specific details
        $pharmacy->name = $validated['name'];
        $pharmacy->location = $validated['location'];
        $pharmacy->bio = $validated['bio'];
        $pharmacy->latitude = $validated['latitude'];
        $pharmacy->longitude = $validated['longitude'];
        $pharmacy->is_available = $validated['is_available'];
        $pharmacy->wilaya_code = $validated['wilaya_code'];
        $pharmacy->save();

        // Check profile complete status
        $isComplete = !empty($pharmacy->name) &&
                      !empty($pharmacy->location) &&
                      !empty($pharmacy->wilaya_code);

        $user->profile_complete = $isComplete;
        $user->save();

        return response()->json([
            'success' => true,
            'profile_complete' => $isComplete,
            'pharmacy' => $pharmacy->load('user'),
        ]);
    }
}
