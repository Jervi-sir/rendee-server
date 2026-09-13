<?php

namespace App\Http\Controllers\V1\Api\Patient;

use App\Http\Controllers\Controller;
use App\Models\LikedPartner;
use App\Models\Partner;
use App\Models\Profession;
use App\Models\Wilaya;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeedController extends Controller
{
    /**
     * GET /api/v1/patient/feed
     *
     * Query Parameters:
     * - `query` (string, optional): Search keyword for name, speciality, city, or bio
     * - `wilaya_code` (string, optional): Filter by Wilaya code (e.g. "16", "31")
     * - `profession` / `profession_code` (string, optional): Filter by Profession code ("doctor", "dentist", "psychologist", etc.)
     * - `speciality` / `speciality_code` (string, optional): Filter by Speciality code ("cardiology", "pediatrics", etc.)
     * - `on_duty` (boolean, optional): Filter for on-duty / emergency partners
     * - `near_me` (boolean, optional): Filter within 50 km of user's coordinates
     * - `lat` / `lat` (float, optional): User lat
     * - `lng` / `lng` (float, optional): User lng
     * - `page` (integer, default: 1)
     * - `per_page` (integer, default: 10)
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // 1. Extract query & filter parameters
        $query = $request->query('query');
        $wilayaCode = $request->query('wilaya_code') ?? $request->query('wilaya');
        $professionCode = $request->query('profession_code') ?? $request->query('profession') ?? $request->query('partner_type') ?? $request->query('user_type');
        $specialityCode = $request->query('speciality_code') ?? $request->query('speciality');
        $onDuty = $request->boolean('on_duty');
        $nearMe = $request->boolean('near_me');

        $lat = $request->query('lat') ?? $request->query('latitude');
        $lng = $request->query('lng') ?? $request->query('longitude');
        $userLat = ($lat !== null && $lat !== '') ? (float) $lat : null;
        $userLng = ($lng !== null && $lng !== '') ? (float) $lng : null;

        $page = max((int) $request->query('page', 1), 1);
        $perPage = max((int) $request->query('per_page', 10), 1);

        // Fetch liked partner IDs for authenticated user
        $likedPartnerIds = [];
        if ($user) {
            $likedPartnerIds = LikedPartner::where('user_id', $user->id)
                ->pluck('partner_id')
                ->toArray();
        }

        // 2. Build base query for active & available partners
        $partnerQuery = Partner::with(['user', 'profession', 'speciality', 'wilaya', 'commune'])
            ->where('is_active', true)
            ->where('is_available', true);

        if ($query) {
            $partnerQuery->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('city', 'like', "%{$query}%")
                    ->orWhere('address', 'like', "%{$query}%")
                    ->orWhere('bio', 'like', "%{$query}%")
                    ->orWhere('custom_speciality', 'like', "%{$query}%")
                    ->orWhereHas('user', function ($uq) use ($query) {
                        $uq->where('full_name', 'like', "%{$query}%")
                            ->orWhere('name', 'like', "%{$query}%");
                    })
                    ->orWhereHas('speciality', function ($sq) use ($query) {
                        $sq->where('ar', 'like', "%{$query}%")
                            ->orWhere('en', 'like', "%{$query}%")
                            ->orWhere('fr', 'like', "%{$query}%")
                            ->orWhere('code', 'like', "%{$query}%");
                    })
                    ->orWhereHas('profession', function ($pq) use ($query) {
                        $pq->where('ar', 'like', "%{$query}%")
                            ->orWhere('en', 'like', "%{$query}%")
                            ->orWhere('fr', 'like', "%{$query}%")
                            ->orWhere('code', 'like', "%{$query}%");
                    });
            });
        }

        if ($wilayaCode && $wilayaCode !== 'all') {
            $partnerQuery->where('wilaya_code', $wilayaCode);
        }

        if ($professionCode && $professionCode !== 'all') {
            $partnerQuery->where('profession_code', $professionCode);
        }

        if ($specialityCode && $specialityCode !== 'all') {
            $partnerQuery->where(function ($sq) use ($specialityCode) {
                $sq->where('speciality_code', $specialityCode)
                    ->orWhere('custom_speciality', 'like', "%{$specialityCode}%");
            });
        }

        if ($onDuty) {
            $partnerQuery->where('is_on_duty', true);
        }

        if ($nearMe && $userLat !== null && $userLng !== null) {
            $partnerQuery->whereNotNull('lat')->whereNotNull('lng');
        }

        $partners = $partnerQuery->get();

        // 3. Format feed items
        $results = [];
        foreach ($partners as $partner) {
            $distance = $this->calculateDistance($userLat, $userLng, $partner->lat, $partner->lng);

            // Filter near_me radius (e.g. within 50 km) if near_me requested and distance available
            if ($nearMe && $userLat !== null && $userLng !== null && ($distance === null || $distance > 50)) {
                continue;
            }

            $name = $partner->name ?? $partner->user?->full_name ?? $partner->user?->name ?? 'شريك';

            $specialityName = $partner->speciality?->ar
                ?? $partner->speciality?->fr
                ?? $partner->custom_speciality
                ?? $partner->display_speciality
                ?? $partner->speciality?->en
                ?? $partner->profession?->ar
                ?? $partner->profession?->fr
                ?? $partner->profession?->en
                ?? 'أخصائي';

            $professionCodeVal = $partner->profession_code ?? 'doctor';
            $professionLabel = $partner->profession?->ar
                ?? $partner->profession?->fr
                ?? $partner->profession?->en
                ?? ($professionCodeVal === 'pharmacist' ? 'صيدلية' : ($professionCodeVal === 'center' ? 'مركز طبي' : 'طبيب'));
            $professionHex = $partner->profession?->hex ?? '#0ea5e9';

            $imageUrl = $this->formatImageUrl($partner->user?->image_url);
            $phone = $partner->phone_public ?? $partner->user?->phone_number;

            $results[] = [
                'id' => (int) $partner->id,
                'name' => $name,
                'phone_number' => $phone,
                'phone' => $phone,
                'profile_pic' => $imageUrl,
                'image_url' => $imageUrl,
                'profession' => [
                    'code' => $professionCodeVal,
                    'label' => $professionLabel,
                    'hex' => $professionHex,
                ],
                'partner_type' => [
                    'code' => $professionCodeVal,
                    'label' => $professionLabel,
                    'hex' => $professionHex,
                ],
                'speciality' => $specialityName,
                'distance' => $distance,
                'rating' => 4.8,
                'reviews_count' => 120,
                'is_liked' => in_array($partner->id, $likedPartnerIds),
                'location' => [
                    'wilaya_name' => $partner->wilaya?->ar ?? $partner->wilaya?->fr ?? $partner->city ?? 'الجزائر',
                    'wilaya_number' => (string) ($partner->wilaya?->code ?? $partner->wilaya_code ?? '16'),
                    'address' => $partner->address ?? $partner->city,
                    'lat' => $partner->lat ? (float) $partner->lat : null,
                    'lng' => $partner->lng ? (float) $partner->lng : null,
                ],
                'is_on_duty' => (bool) $partner->is_on_duty,
            ];
        }

        // 4. Sort results by distance if location provided
        if ($userLat !== null && $userLng !== null) {
            usort($results, function ($a, $b) {
                $distA = $a['distance'] ?? INF;
                $distB = $b['distance'] ?? INF;

                return $distA <=> $distB;
            });
        }

        // 5. Generate counts for filters dynamically based on Professions
        $professions = Profession::all();

        $filters = [
            [
                'key' => 'all',
                'label' => 'الكل',
                'count' => count($results),
                'hex' => null,
            ],
        ];

        foreach ($professions as $prof) {
            $code = $prof->code;
            $label = $prof->ar ?? $prof->fr ?? $prof->en ?? $code;
            $count = count(array_filter($results, fn ($i) => ($i['profession']['code'] ?? '') === $code));

            $filters[] = [
                'key' => $code,
                'label' => $label,
                'count' => $count,
                'hex' => $prof->hex,
            ];
        }

        // 6. Paginate results
        $total = count($results);
        $offset = ($page - 1) * $perPage;
        $pagedResults = array_slice($results, $offset, $perPage);
        $nextPage = ($offset + $perPage < $total) ? $page + 1 : null;

        return response()->json([
            'results' => array_values($pagedResults),
            'filters' => $filters,
            'wilayas' => Wilaya::getActiveWilayas(),
            'current_page' => $page,
            'next_page' => $nextPage,
            'total' => $total,
        ]);
    }

    /**
     * Format image URL to always return an absolute/full URL.
     */
    private function formatImageUrl(?string $imageUrl): ?string
    {
        if (! $imageUrl) {
            return null;
        }

        if (str_starts_with($imageUrl, 'http://') || str_starts_with($imageUrl, 'https://')) {
            return $imageUrl;
        }

        return url($imageUrl);
    }

    private function calculateDistance(?float $lat1, ?float $lon1, $lat2, $lon2): ?float
    {
        if (is_null($lat1) || is_null($lon1) || is_null($lat2) || is_null($lon2)) {
            return null;
        }

        $earthRadius = 6371; // in kilometers

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad((float) $lat2);
        $lonTo = deg2rad((float) $lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return round($angle * $earthRadius, 1);
    }
}
