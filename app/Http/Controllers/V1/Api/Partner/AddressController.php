<?php

namespace App\Http\Controllers\V1\Api\Partner;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Models\Wilaya;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AddressController extends Controller
{
    /**
     * Show address details for authenticated partner and list of wilayas.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $provider = null;

        if ($user) {
            $provider = Partner::with('wilaya')->where('user_id', $user->id)->first();
        }

        if (! $provider) {
            $provider = Partner::with('wilaya')->first();
        }

        $wilayas = Wilaya::orderBy('number', 'asc')->get()->map(function ($w) {
            return [
                'code' => $w->code,
                'number' => $w->number,
                'label' => $w->ar ?? $w->en ?? $w->code,
                'ar' => $w->ar,
                'en' => $w->en,
                'fr' => $w->fr,
            ];
        });

        if (! $provider) {
            return response()->json([
                'success' => true,
                'address' => [
                    'address' => '',
                    'city' => '',
                    'wilaya_code' => null,
                    'wilaya_name' => null,
                    'latitude' => null,
                    'longitude' => null,
                ],
                'wilayas' => $wilayas,
            ]);
        }

        return response()->json([
            'success' => true,
            'address' => [
                'address' => $provider->address ?? '',
                'city' => $provider->city ?? '',
                'wilaya_code' => $provider->wilaya_code,
                'wilaya_name' => $provider->wilaya?->ar ?? $provider->wilaya?->en ?? null,
                'latitude' => $provider->latitude ? (float) $provider->latitude : null,
                'longitude' => $provider->longitude ? (float) $provider->longitude : null,
            ],
            'wilayas' => $wilayas,
        ]);
    }

    /**
     * Create or update (upsert) address details for authenticated partner.
     */
    public function upsert(Request $request): JsonResponse
    {
        $user = $request->user();
        $provider = null;

        if ($user) {
            $provider = Partner::where('user_id', $user->id)->first();
        }

        if (! $provider) {
            $provider = Partner::first();
        }

        if (! $provider) {
            return response()->json(['error' => 'Partner profile not found'], 404);
        }

        $validated = $request->validate([
            'address' => ['required', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:100'],
            'wilaya_code' => ['nullable', 'string', Rule::exists('wilayas', 'code')],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $provider->address = $validated['address'];
        $provider->city = $validated['city'];
        $provider->wilaya_code = $validated['wilaya_code'] ?? $provider->wilaya_code;
        if (array_key_exists('latitude', $validated)) {
            $provider->latitude = $validated['latitude'];
        }
        if (array_key_exists('longitude', $validated)) {
            $provider->longitude = $validated['longitude'];
        }
        $provider->save();

        $provider->load('wilaya');

        return response()->json([
            'success' => true,
            'message' => 'Address details updated successfully.',
            'address' => [
                'address' => $provider->address,
                'city' => $provider->city,
                'wilaya_code' => $provider->wilaya_code,
                'wilaya_name' => $provider->wilaya?->ar ?? $provider->wilaya?->en ?? null,
                'latitude' => $provider->latitude ? (float) $provider->latitude : null,
                'longitude' => $provider->longitude ? (float) $provider->longitude : null,
            ],
        ]);
    }
}
