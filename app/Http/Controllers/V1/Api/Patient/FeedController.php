<?php

namespace App\Http\Controllers\V1\Api\Patient;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeedController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // 1. Extract query & filter parameters
        $query = $request->query('query');
        $wilayaCode = $request->query('wilaya_code') ?? $request->query('wilaya');
        $partnerType = $request->query('partner_type') ?? $request->query('user_type');
        $onDuty = $request->boolean('on_duty');
        $nearMe = $request->boolean('near_me');

        $lat = $request->query('lat') ?? $request->query('latitude');
        $lng = $request->query('lng') ?? $request->query('longitude');
        $userLat = $lat !== null ? (float) $lat : null;
        $userLng = $lng !== null ? (float) $lng : null;

        $page = max((int) $request->query('page', 1), 1);
        $perPage = max((int) $request->query('per_page', 10), 1);

        // Fetch liked partner IDs for authenticated user
        $likedPartnerIds = [];
        if ($user) {
            $likedPartnerIds = \App\Models\LikeItem::where('user_id', $user->id)
                ->where('likeable_type', \App\Models\Partner::class)
                ->pluck('likeable_id')
                ->toArray();
        }

        // 2. Build base query for active & available partners
        $partnerQuery = \App\Models\Partner::with(['user', 'partnerType', 'speciality', 'catalog', 'wilaya'])
            ->where('is_active', true)
            ->where('is_available', true);

        if ($query) {
            $partnerQuery->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('city', 'like', "%{$query}%")
                    ->orWhere('address', 'like', "%{$query}%")
                    ->orWhere('bio', 'like', "%{$query}%")
                    ->orWhereHas('user', function ($uq) use ($query) {
                        $uq->where('full_name', 'like', "%{$query}%")
                            ->orWhere('name', 'like', "%{$query}%");
                    })
                    ->orWhereHas('speciality', function ($sq) use ($query) {
                        $sq->where('ar', 'like', "%{$query}%")
                            ->orWhere('en', 'like', "%{$query}%")
                            ->orWhere('code', 'like', "%{$query}%");
                    })
                    ->orWhereHas('catalog', function ($cq) use ($query) {
                        $cq->where('ar', 'like', "%{$query}%")
                            ->orWhere('en', 'like', "%{$query}%")
                            ->orWhere('code', 'like', "%{$query}%");
                    });
            });
        }

        if ($wilayaCode) {
            $partnerQuery->where('wilaya_code', $wilayaCode);
        }

        if ($partnerType && $partnerType !== 'all') {
            $partnerQuery->where('partner_type_code', $partnerType);
        }

        if ($onDuty) {
            $partnerQuery->where('is_on_duty', true);
        }

        if ($nearMe && $userLat !== null && $userLng !== null) {
            $partnerQuery->whereNotNull('latitude')->whereNotNull('longitude');
        }

        $partners = $partnerQuery->get();

        // 3. Format feed items
        $results = [];
        foreach ($partners as $partner) {
            $distance = $this->calculateDistance($userLat, $userLng, $partner->latitude, $partner->longitude);

            // Filter near_me radius (e.g. within 50 km) if near_me requested and distance available
            if ($nearMe && $userLat !== null && $userLng !== null && ($distance === null || $distance > 50)) {
                continue;
            }

            $name = $partner->name ?? $partner->user?->full_name ?? $partner->user?->name ?? 'شريك';
            if (in_array($partner->partner_type_code, ['doctor', 'professional']) && ! str_starts_with($name, 'د.')) {
                $name = 'د. ' . $name;
            }

            $specialityName = $partner->speciality?->ar
                ?? $partner->speciality?->en
                ?? $partner->catalog?->ar
                ?? $partner->catalog?->en
                ?? $partner->profession?->ar
                ?? $partner->profession?->en
                ?? 'أخصائي';

            $partnerTypeCode = $partner->partner_type_code ?? 'doctor';
            $partnerTypeLabel = $partner->partnerType?->ar
                ?? $partner->partnerType?->en
                ?? ($partnerTypeCode === 'pharmacist' ? 'صيدلية' : ($partnerTypeCode === 'center' ? 'مركز طبي' : 'طبيب'));

            $results[] = [
                'id' => $partner->id,
                'name' => $name,
                'profile_pic' => $partner->user?->image_url,
                'partner_type' => [
                    'code' => $partnerTypeCode,
                    'label' => $partnerTypeLabel,
                ],
                'speciality' => $specialityName,
                'distance' => $distance,
                'rating' => 4.8,
                'reviews_count' => 120,
                'is_liked' => in_array($partner->id, $likedPartnerIds),
                'location' => [
                    'wilaya_name' => $partner->wilaya?->ar ?? $partner->wilaya?->en ?? $partner->city ?? 'وهران',
                    'wilaya_number' => (string) ($partner->wilaya?->number ?? $partner->wilaya_code ?? '31'),
                    'address' => $partner->address ?? $partner->city,
                    'lat' => $partner->latitude ? (float) $partner->latitude : null,
                    'lng' => $partner->longitude ? (float) $partner->longitude : null,
                ],
                'is_on_duty' => (bool) $partner->is_on_duty,
            ];
        }

        // 4. Sort results by distance if location provided, otherwise created_at / default
        if ($userLat !== null && $userLng !== null) {
            usort($results, function ($a, $b) {
                $distA = $a['distance'] ?? INF;
                $distB = $b['distance'] ?? INF;

                return $distA <=> $distB;
            });
        }

        // 5. Generate counts for filters dynamically from PartnerType or all type codes
        $typeLabels = [
            'doctor' => 'أطباء',
            'dentist' => 'أطباء الأسنان',
            'pharmacist' => 'صيدليات',
            'center' => 'مراكز طبية',
            'psy' => 'أطباء نفسيون',
            'professional' => 'مهنيون',
        ];

        $filters = [
            [
                'key' => 'all',
                'label' => 'الكل',
                'count' => count($results),
            ],
        ];

        foreach ($typeLabels as $code => $defaultLabel) {
            $partnerTypeObj = \App\Models\PartnerType::find($code);
            $label = $partnerTypeObj?->ar ?? $partnerTypeObj?->en ?? $defaultLabel;
            $count = count(array_filter($results, fn($i) => $i['partner_type']['code'] === $code));

            $filters[] = [
                'key' => $code,
                'label' => $label,
                'count' => $count,
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
            'wilayas' => \App\Models\Wilaya::getActiveWilayas(),
            'current_page' => $page,
            'next_page' => $nextPage,
            'total' => $total,
        ]);
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
