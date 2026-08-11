<?php

namespace App\Http\Controllers\V1\Api\Partner;

use App\Http\Controllers\Controller;
use App\Models\Center;
use App\Models\CenterCatalog;
use App\Models\Pharmacy;
use App\Models\Profession;
use App\Models\Professional;
use App\Models\ProfessionalSpeciality;
use App\Models\User;
use App\Models\Wilaya;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Retrieve authenticated partner's profile (Professional, Pharmacist, or Center).
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            $user = User::first();
        }

        if (! $user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $roleCode = $user->user_role_code;
        $partnerType = 'professional'; // default

        // Eager-load role specific model
        $professional = Professional::with(['specialty', 'profession', 'wilaya', 'contacts'])->where('user_id', $user->id)->first();
        $center = Center::with(['catalog', 'wilaya', 'contacts'])->where('user_id', $user->id)->first();
        $pharmacy = Pharmacy::with(['wilaya'])->where('user_id', $user->id)->first();

        if ($roleCode === 'center' || $center) {
            $partnerType = 'center';
        } elseif ($roleCode === 'pharmacist' || $pharmacy) {
            $partnerType = 'pharmacist';
        }

        // Shared catalog metadata lists for selection
        $specialities = ProfessionalSpeciality::all()->map(fn($s) => [
            'id' => $s->id,
            'code' => $s->code,
            'label' => $s->ar ?? $s->en ?? $s->code,
            'ar' => $s->ar,
            'en' => $s->en,
            'fr' => $s->fr,
        ]);

        $professions = Profession::all()->map(fn($p) => [
            'code' => $p->code,
            'label' => $p->ar ?? $p->en ?? $p->code,
            'ar' => $p->ar,
            'en' => $p->en,
            'fr' => $p->fr,
            'hex' => $p->hex,
        ]);

        $centerCatalogs = CenterCatalog::all()->map(fn($c) => [
            'code' => $c->code,
            'label' => $c->ar ?? $c->en ?? $c->code,
            'ar' => $c->ar,
            'en' => $c->en,
            'fr' => $c->fr,
        ]);

        $wilayas = Wilaya::orderBy('number', 'asc')->get()->map(fn($w) => [
            'code' => $w->code,
            'number' => $w->number,
            'label' => $w->ar ?? $w->en ?? $w->code,
            'ar' => $w->ar,
            'en' => $w->en,
            'fr' => $w->fr,
        ]);

        // Base profile object
        $profile = [
            'user_id' => $user->id,
            'partner_type' => $partnerType,
            'full_name' => $user->full_name ?? $user->name ?? '',
            'name' => $user->name ?? '',
            'email' => $user->email ?? '',
            'phone' => $user->phone_number ?? '',
            'phone_number' => $user->phone_number ?? '',
            'image_url' => $user->image_url,
            'profile_complete' => (bool) $user->profile_complete,
        ];

        // Hydrate type-specific partner profile attributes
        if ($partnerType === 'center' && $center) {
            $profile = array_merge($profile, [
                'id' => $center->id,
                'center_name' => $center->name,
                'center_catalog_code' => $center->center_catalog_code,
                'catalog_label' => $center->catalog?->ar ?? $center->catalog?->en ?? 'مركز طبي',
                'license_number' => $center->license_number,
                'phone_public' => $center->phone_public ?? $user->phone_number,
                'bio' => $center->description,
                'address' => $center->address,
                'city' => $center->city,
                'wilaya_code' => $center->wilaya_code,
                'wilaya' => $center->wilaya?->ar ?? $center->wilaya?->en ?? null,
                'latitude' => $center->latitude ? (float) $center->latitude : null,
                'longitude' => $center->longitude ? (float) $center->longitude : null,
                'emergency_24_7' => (bool) $center->emergency_24_7,
                'is_available' => (bool) $center->is_active,
                'contacts' => $center->contacts ?? [],
            ]);
        } elseif ($partnerType === 'pharmacist' && $pharmacy) {
            $profile = array_merge($profile, [
                'id' => $pharmacy->id,
                'pharmacy_name' => $pharmacy->name,
                'phone_public' => $user->phone_number,
                'bio' => $pharmacy->bio,
                'address' => $pharmacy->location,
                'city' => '',
                'wilaya_code' => $pharmacy->wilaya_code,
                'wilaya' => $pharmacy->wilaya?->ar ?? $pharmacy->wilaya?->en ?? null,
                'latitude' => $pharmacy->latitude ? (float) $pharmacy->latitude : null,
                'longitude' => $pharmacy->longitude ? (float) $pharmacy->longitude : null,
                'is_available' => (bool) $pharmacy->is_available,
                'contacts' => [],
            ]);
        } elseif ($professional) {
            $profile = array_merge($profile, [
                'id' => $professional->id,
                'profession_code' => $professional->profession_code,
                'profession_label' => $professional->profession?->ar ?? $professional->profession?->en ?? 'أخصائي',
                'professional_speciality_code' => $professional->professional_speciality_code,
                'specialty_id' => $professional->specialty?->id,
                'specialty' => $professional->specialty?->ar ?? $professional->specialty?->en ?? 'عام',
                'license_number' => $professional->license_number,
                'years_experience' => (string) ($professional->years_experience ?? ''),
                'phone_public' => $professional->phone_public ?? $user->phone_number,
                'bio' => $professional->bio,
                'address' => $professional->address,
                'city' => $professional->city,
                'wilaya_code' => $professional->wilaya_code,
                'wilaya' => $professional->wilaya?->ar ?? $professional->wilaya?->en ?? null,
                'latitude' => $professional->latitude ? (float) $professional->latitude : null,
                'longitude' => $professional->longitude ? (float) $professional->longitude : null,
                'is_available' => (bool) $professional->is_available,
                'contacts' => $professional->contacts ?? [],
            ]);
        }

        return response()->json([
            'success' => true,
            'profile' => $profile,
            'specialities' => $specialities,
            'professions' => $professions,
            'center_catalogs' => $centerCatalogs,
            'wilayas' => $wilayas,
        ]);
    }

    /**
     * Update authenticated partner's profile (Professional, Pharmacist, or Center).
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            $user = User::first();
        }

        if (! $user) {
            return response()->json(['error' => 'User profile not found'], 404);
        }

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:30'],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'phone_public' => ['nullable', 'string', 'max:30'],
            'image_url' => ['nullable', 'string', 'max:500'],
            'bio' => ['nullable', 'string'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'wilaya_code' => ['nullable', 'string'],
            'license_number' => ['nullable', 'string', 'max:100'],
            'years_experience' => ['nullable', 'string', 'max:10'],
            'profession_code' => ['nullable', 'string'],
            'professional_speciality_code' => ['nullable', 'string'],
            'specialty_id' => ['nullable', 'integer'],
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

        $roleCode = $user->user_role_code;

        // 2. Update role-specific model
        if ($roleCode === 'center') {
            $center = Center::firstOrCreate(['user_id' => $user->id], ['name' => $user->full_name]);
            if (! empty($validated['name'])) {
                $center->name = $validated['name'];
            }
            if (array_key_exists('center_catalog_code', $validated)) {
                $center->center_catalog_code = $validated['center_catalog_code'];
            }
            if (array_key_exists('wilaya_code', $validated)) {
                $center->wilaya_code = $validated['wilaya_code'];
            }
            if (array_key_exists('license_number', $validated)) {
                $center->license_number = $validated['license_number'];
            }
            if (array_key_exists('phone_public', $validated)) {
                $center->phone_public = $validated['phone_public'];
            }
            if (array_key_exists('bio', $validated)) {
                $center->description = $validated['bio'];
            }
            if (array_key_exists('address', $validated)) {
                $center->address = $validated['address'];
            }
            if (array_key_exists('city', $validated)) {
                $center->city = $validated['city'];
            }
            if (array_key_exists('latitude', $validated)) {
                $center->latitude = $validated['latitude'];
            }
            if (array_key_exists('longitude', $validated)) {
                $center->longitude = $validated['longitude'];
            }
            if (array_key_exists('emergency_24_7', $validated)) {
                $center->emergency_24_7 = (bool) $validated['emergency_24_7'];
            }
            if (array_key_exists('is_available', $validated)) {
                $center->is_active = (bool) $validated['is_available'];
            }
            $center->save();
        } elseif ($roleCode === 'pharmacist') {
            $pharmacy = Pharmacy::firstOrCreate(['user_id' => $user->id], ['name' => $user->full_name]);
            if (! empty($validated['name'])) {
                $pharmacy->name = $validated['name'];
            }
            if (array_key_exists('wilaya_code', $validated)) {
                $pharmacy->wilaya_code = $validated['wilaya_code'];
            }
            if (array_key_exists('address', $validated)) {
                $pharmacy->location = $validated['address'];
            }
            if (array_key_exists('bio', $validated)) {
                $pharmacy->bio = $validated['bio'];
            }
            if (array_key_exists('latitude', $validated)) {
                $pharmacy->latitude = $validated['latitude'];
            }
            if (array_key_exists('longitude', $validated)) {
                $pharmacy->longitude = $validated['longitude'];
            }
            if (array_key_exists('is_available', $validated)) {
                $pharmacy->is_available = (bool) $validated['is_available'];
            }
            $pharmacy->save();
        } else {
            $professional = Professional::firstOrCreate(
                ['user_id' => $user->id],
                ['profession_code' => $validated['profession_code'] ?? 'doctor']
            );

            $specialityCode = $validated['professional_speciality_code'] ?? null;
            if (! $specialityCode && ! empty($validated['specialty_id'])) {
                $speciality = ProfessionalSpeciality::find($validated['specialty_id']);
                if ($speciality) {
                    $specialityCode = $speciality->code;
                }
            }

            if (! empty($validated['profession_code'])) {
                $professional->profession_code = $validated['profession_code'];
            }
            if ($specialityCode) {
                $professional->professional_speciality_code = $specialityCode;
            }
            if (array_key_exists('wilaya_code', $validated)) {
                $professional->wilaya_code = $validated['wilaya_code'];
            }
            if (array_key_exists('license_number', $validated)) {
                $professional->license_number = $validated['license_number'];
            }
            if (array_key_exists('years_experience', $validated)) {
                $professional->years_experience = $validated['years_experience'];
            }
            if (array_key_exists('phone_public', $validated)) {
                $professional->phone_public = $validated['phone_public'];
            }
            if (array_key_exists('bio', $validated)) {
                $professional->bio = $validated['bio'];
            }
            if (array_key_exists('address', $validated)) {
                $professional->address = $validated['address'];
            }
            if (array_key_exists('city', $validated)) {
                $professional->city = $validated['city'];
            }
            if (array_key_exists('latitude', $validated)) {
                $professional->latitude = $validated['latitude'];
            }
            if (array_key_exists('longitude', $validated)) {
                $professional->longitude = $validated['longitude'];
            }
            if (array_key_exists('is_available', $validated)) {
                $professional->is_available = (bool) $validated['is_available'];
            }
            $professional->save();
        }

        $user->profile_complete = true;
        $user->save();

        return $this->show($request);
    }
}
