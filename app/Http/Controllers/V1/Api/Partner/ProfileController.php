<?php

namespace App\Http\Controllers\V1\Api\Partner;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function preview(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            $user = User::first();
        }
        if (! $user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $partner = Partner::with(['partnerType', 'speciality', 'catalog'])->where('user_id', $user->id)->first();
        if (! $partner) {
            $partner = Partner::with(['partnerType', 'speciality', 'catalog'])->first();
        }

        $specialityName = $partner?->speciality?->ar
            ?? $partner?->speciality?->en
            ?? $partner?->catalog?->ar
            ?? $partner?->catalog?->en
            ?? '';

        $partnerTypeName = $partner?->partnerType?->ar
            ?? $partner?->partnerType?->en
            ?? $partner?->partner_type_code
            ?? '';

        $profile = [
            'partner_type_code' => $partner?->partner_type_code ?? '',
            'partner_type' => $partnerTypeName,
            'full_name' => $user->full_name ?? $user->name ?? '',
            'email' => $user->email ?? '',
            'phone_number' => $user->phone_number ?? '',
            'image_url' => $user->image_url,
            'speciality' => $specialityName,
            'bio' => $partner?->bio ?? '',

            'profile_completed' => (bool) $user->profile_completed,
            'emergency_24_7' => (bool) ($partner?->emergency_24_7 ?? false),
            'is_available' => (bool) ($partner?->is_available ?? false),
            'is_active' => (bool) ($partner?->is_active ?? false),
            'is_approved' => (bool) ($partner?->is_active ?? false),
        ];

        return response()->json([
            'success' => true,
            'profile' => $profile,
        ]);
    }

    /**
     * Fetch complete unified profile data for authenticated user/partner.
     */
    public function show(Request $request): JsonResponse
    {
        [$user, $partner] = $this->resolveUserAndPartner($request);

        if (! $user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        if (! $partner) {
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
        }

        $partner->load([
            'profession',
            'speciality',
            'catalog',
            'wilaya',
            'schedules',
            'services',
            'contacts.platform',
        ]);

        $profileData = [
            'id' => $user->id,
            'partner_id' => $partner->id,
            'name' => $user->name ?? '',
            'full_name' => $user->full_name ?? '',
            'email' => $user->email ?? '',
            'phone_number' => $user->phone_number ?? '',
            'image_url' => $user->image_url,
            'profile_completed' => (bool) $user->profile_completed,

            'profession_code' => $partner->profession_code,
            'profession_label' => $partner->profession?->ar ?? $partner->profession?->en ?? 'أخصائي',
            'speciality_code' => $partner->speciality_code,
            'custom_speciality' => $partner->custom_speciality,
            'specialty' => $partner->display_speciality ?? 'عام',
            'center_catalog_code' => $partner->center_catalog_code,
            'catalog_label' => $partner->catalog?->ar ?? $partner->catalog?->en ?? 'مركز طبي',
            'license_number' => $partner->license_number,
            'years_experience' => (string) ($partner->years_experience ?? ''),
            'bio' => $partner->bio,
            'description' => $partner->bio,
            'address' => $partner->address,
            'city' => $partner->city,
            'wilaya_code' => $partner->wilaya_code,
            'wilaya' => $partner->wilaya?->ar ?? $partner->wilaya?->en ?? null,
            'latitude' => $partner->latitude ? (float) $partner->latitude : null,
            'longitude' => $partner->longitude ? (float) $partner->longitude : null,
            'emergency_24_7' => (bool) $partner->emergency_24_7,
            'is_available' => (bool) $partner->is_available,
            'is_active' => (bool) $partner->is_active,
            'is_on_duty' => (bool) $partner->is_on_duty,
            'phone_public' => $partner->phone_public,
            'user_role_code' => $user->user_role_code,
            'partner_type_code' => $partner->partner_type_code,
            'services' => $partner->services,
            'schedules' => $partner->schedules,
            'contacts' => $partner->contacts,
        ];

        return response()->json($profileData);
    }

    /**
     * Unified update for all partner & user profile information.
     */
    public function update(Request $request): JsonResponse
    {
        [$user, $partner] = $this->resolveUserAndPartner($request);

        if (! $user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'phone' => ['nullable', 'string', 'max:30'],
            'phone_public' => ['nullable', 'string', 'max:30'],
            'image_url' => ['nullable', 'string', 'max:500'],
            'bio' => ['nullable', 'string'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'wilaya_code' => ['nullable', 'string'],
            'license_number' => ['nullable', 'string', 'max:100'],
            'years_experience' => ['nullable', 'string', 'max:10'],
            'profession_code' => ['nullable', 'string'],
            'speciality_code' => ['nullable', 'string'],
            'professional_speciality_code' => ['nullable', 'string'],
            'custom_speciality' => ['nullable', 'string', 'max:255'],
            'specialty_id' => ['nullable', 'string'],
            'center_catalog_code' => ['nullable', 'string'],
            'emergency_24_7' => ['nullable', 'boolean'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'is_available' => ['nullable', 'boolean'],
        ]);

        // 1. Update core User attributes
        $user->full_name = $validated['full_name'];
        if (! empty($validated['name'])) {
            $user->name = $validated['name'];
        }
        $user->email = $validated['email'];
        $user->phone_number = $validated['phone_number'] ?? $validated['phone'] ?? $user->phone_number;
        if (array_key_exists('image_url', $validated)) {
            $user->image_url = $validated['image_url'];
        }
        $user->save();

        // 2. Update Partner profile
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

        $specialityCode = $validated['speciality_code'] ?? $validated['professional_speciality_code'] ?? $validated['specialty_id'] ?? null;

        if (! empty($validated['name'])) {
            $partner->name = $validated['name'];
        }
        if (! empty($validated['profession_code'])) {
            $partner->profession_code = $validated['profession_code'];
        }
        if (array_key_exists('speciality_code', $validated) || array_key_exists('professional_speciality_code', $validated) || array_key_exists('specialty_id', $validated)) {
            $partner->speciality_code = $specialityCode;
        }
        if (array_key_exists('custom_speciality', $validated)) {
            $partner->custom_speciality = $validated['custom_speciality'];
        }
        if (array_key_exists('center_catalog_code', $validated)) {
            $partner->center_catalog_code = $validated['center_catalog_code'];
        }
        if (array_key_exists('wilaya_code', $validated)) {
            $partner->wilaya_code = $validated['wilaya_code'];
        }
        if (array_key_exists('license_number', $validated)) {
            $partner->license_number = $validated['license_number'];
        }
        if (array_key_exists('years_experience', $validated)) {
            $partner->years_experience = $validated['years_experience'];
        }
        if (array_key_exists('phone_public', $validated)) {
            $partner->phone_public = $validated['phone_public'];
        }
        if (array_key_exists('bio', $validated)) {
            $partner->bio = $validated['bio'];
        }
        if (array_key_exists('address', $validated)) {
            $partner->address = $validated['address'];
        }
        if (array_key_exists('city', $validated)) {
            $partner->city = $validated['city'];
        }
        if (array_key_exists('latitude', $validated)) {
            $partner->latitude = $validated['latitude'];
        }
        if (array_key_exists('longitude', $validated)) {
            $partner->longitude = $validated['longitude'];
        }
        if (array_key_exists('emergency_24_7', $validated)) {
            $partner->emergency_24_7 = (bool) $validated['emergency_24_7'];
        }
        if (array_key_exists('is_available', $validated)) {
            $partner->is_available = (bool) $validated['is_available'];
            $partner->is_active = (bool) $validated['is_available'];
        }

        $partner->save();

        $user->profile_completed = true;
        $user->save();

        return $this->show($request);
    }

    /**
     * Helper to resolve current user and associated partner.
     */
    private function resolveUserAndPartner(Request $request): array
    {
        $user = $request->user();
        $partner = null;

        if ($user) {
            $partner = Partner::where('user_id', $user->id)->first();
        }

        if (! $partner) {
            $partner = Partner::first();
            if ($partner) {
                $user = User::find($partner->user_id);
            }
        }

        return [$user, $partner];
    }
}
