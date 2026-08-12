<?php

namespace App\Http\Controllers\V1\Api\Patient;

use App\Http\Controllers\Controller;
use App\Models\LikeItem;
use App\Models\Partner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeedController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user('sanctum') ?? $request->user();
        $userLikedMap = [];

        if ($user) {
            $likedItems = LikeItem::where('user_id', $user->id)->get();
            foreach ($likedItems as $like) {
                $key = $like->likeable_type.':'.$like->likeable_id;
                $userLikedMap[$key] = true;
            }
        }

        $query = $request->query('query');
        $userLatitude = $request->query('latitude');
        $userLongitude = $request->query('longitude');
        $nearMe = filter_var($request->query('near_me', false), FILTER_VALIDATE_BOOLEAN);

        $userWilaya = $request->query('wilaya');
        $userType = $request->query('user_type'); // partner type code or category
        $onDuty = $request->query('on_duty') ? filter_var($request->query('on_duty'), FILTER_VALIDATE_BOOLEAN) : null;
        $minRating = $request->query('min_rating') ? (float) $request->query('min_rating') : null;

        $partnerQuery = Partner::with(['user', 'speciality', 'profession', 'catalog', 'wilaya', 'partnerType'])
            ->where('is_active', true)
            ->where('is_available', true);

        if ($userType) {
            if (in_array($userType, ['professional', 'doctor'])) {
                $partnerQuery->whereIn('partner_type_code', ['doctor', 'professional', 'dentist', 'psy']);
            } elseif ($userType === 'center') {
                $partnerQuery->where('partner_type_code', 'center');
            } elseif (in_array($userType, ['pharmacy', 'pharmacist'])) {
                $partnerQuery->where('partner_type_code', 'pharmacist');
            } else {
                $partnerQuery->where('partner_type_code', $userType);
            }
        }

        if ($query) {
            $partnerQuery->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('bio', 'like', "%{$query}%")
                    ->orWhere('address', 'like', "%{$query}%")
                    ->orWhere('city', 'like', "%{$query}%")
                    ->orWhereHas('user', function ($uq) use ($query) {
                        $uq->where('full_name', 'like', "%{$query}%")
                            ->orWhere('name', 'like', "%{$query}%");
                    })
                    ->orWhereHas('speciality', function ($sq) use ($query) {
                        $sq->where('ar', 'like', "%{$query}%")
                            ->orWhere('en', 'like', "%{$query}%");
                    })
                    ->orWhereHas('catalog', function ($cq) use ($query) {
                        $cq->where('ar', 'like', "%{$query}%")
                            ->orWhere('en', 'like', "%{$query}%");
                    });
            });
        }

        if ($userWilaya && $userWilaya !== 'all') {
            $partnerQuery->where(function ($q) use ($userWilaya) {
                $q->where('wilaya_code', $userWilaya)
                    ->orWhere('city', 'like', "%{$userWilaya}%")
                    ->orWhere('address', 'like', "%{$userWilaya}%")
                    ->orWhereHas('wilaya', function ($wq) use ($userWilaya) {
                        $wq->where('code', $userWilaya)
                            ->orWhere('number', $userWilaya)
                            ->orWhere('ar', 'like', "%{$userWilaya}%")
                            ->orWhere('en', 'like', "%{$userWilaya}%");
                    });
            });
        }

        $results = [];

        foreach ($partnerQuery->get() as $partner) {
            $distance = $this->calculateDistance($userLatitude, $userLongitude, $partner->latitude, $partner->longitude);

            if ($nearMe && $distance === null) {
                continue;
            }

            $rating = isset($partner->rating) ? (float) $partner->rating : 4.8;
            $reviewsCount = isset($partner->reviews_count) ? (int) $partner->reviews_count : 0;
            $isOnDuty = (bool) ($partner->is_on_duty || $partner->emergency_24_7);

            if ($onDuty && ! $isOnDuty) {
                continue;
            }
            if ($minRating !== null && $rating < $minRating) {
                continue;
            }

            $partnerName = $partner->name ?? $partner->user?->full_name ?? $partner->user?->name ?? 'شريك';
            $partnerTypeCode = $partner->partner_type_code ?? 'doctor';
            if (in_array($partnerTypeCode, ['doctor', 'professional']) && ! str_starts_with($partnerName, 'د.')) {
                $partnerName = 'د. '.$partnerName;
            }

            $typeLabel = 'شريك مخصص';
            if ($partnerTypeCode === 'center') {
                $typeLabel = 'مركز طبي متكامل';
            } elseif ($partnerTypeCode === 'pharmacist') {
                $typeLabel = $isOnDuty ? 'صيدلية مناوبة' : 'صيدلية';
            } else {
                $typeLabel = $partner->profession_code === 'doctor' ? 'طبيب أخصائي' : 'أخصائي';
            }

            $specialty = 'عام';
            if ($partnerTypeCode === 'center') {
                $specialty = $partner->catalog?->ar ?? $partner->catalog?->en ?? 'مركز طبي';
            } elseif ($partnerTypeCode === 'pharmacist') {
                $specialty = 'صيدلية';
            } else {
                $specialty = $partner->speciality?->ar ?? $partner->speciality?->en ?? 'استشارة طبية';
            }

            $isLiked = isset($userLikedMap[Partner::class.':'.$partner->id]);

            $partnerTypeJson = $partner->partnerType ? [
                'code' => $partner->partnerType->code,
                'en' => $partner->partnerType->en,
                'fr' => $partner->partnerType->fr,
                'ar' => $partner->partnerType->ar,
            ] : [
                'code' => $partnerTypeCode,
                'en' => ucfirst($partnerTypeCode),
                'fr' => ucfirst($partnerTypeCode),
                'ar' => $typeLabel,
            ];

            $results[] = [
                'id' => $partner->id,
                'name' => $partnerName,
                'fullname' => $partnerName,
                'type' => $partnerTypeCode,
                'user_type' => $partnerTypeCode,
                'partner_type_code' => $partnerTypeCode,
                'partner_type' => $partnerTypeJson,
                'typeLabel' => $typeLabel,
                'type_label' => $typeLabel,
                'specialty' => $specialty,
                'speciality' => $specialty,
                'distance' => $distance,
                'distance_text' => $distance !== null ? "{$distance} كم" : null,
                'rating' => $rating,
                'reviews_count' => $reviewsCount,
                'isOnDuty' => $isOnDuty,
                'is_on_duty' => $isOnDuty,
                'is_liked' => $isLiked,
                'is_favorite' => $isLiked,
                'wilaya' => $partner->wilaya?->ar ?? $partner->wilaya?->en ?? $partner->city,
                'address' => $partner->address ?? $partner->city,
                'location' => [
                    'wilaya_name' => $partner->wilaya?->ar ?? $partner->wilaya?->en ?? $partner->city,
                    'wilaya_number' => $partner->wilaya?->number ? (int) $partner->wilaya->number : null,
                    'address' => $partner->address ?? $partner->city,
                ],
                'images' => $partner->user?->image_url ? [$partner->user->image_url] : [],
                'created_at' => $partner->created_at?->toIso8601String(),
            ];
        }

        // Sort Results (by distance if coordinates provided, else created_at desc)
        usort($results, function ($a, $b) use ($userLatitude, $userLongitude, $nearMe) {
            if (($userLatitude !== null && $userLongitude !== null) || $nearMe) {
                $distA = $a['distance'] ?? INF;
                $distB = $b['distance'] ?? INF;
                if ($distA !== $distB) {
                    return $distA <=> $distB;
                }
            }

            $timeA = $a['created_at'] ? strtotime($a['created_at']) : 0;
            $timeB = $b['created_at'] ? strtotime($b['created_at']) : 0;

            return $timeB <=> $timeA;
        });

        // Pagination
        $page = max((int) $request->query('page', 1), 1);
        $perPage = max((int) $request->query('per_page', 10), 1);
        $total = count($results);
        $offset = ($page - 1) * $perPage;
        $pagedResults = array_slice($results, $offset, $perPage);
        $nextPage = ($offset + $perPage < $total) ? $page + 1 : null;

        return response()->json([
            'results' => $pagedResults,
            'current_page' => $page,
            'next_page' => $nextPage,
            'total' => $total,
        ]);
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2): ?float
    {
        if (is_null($lat1) || is_null($lon1) || is_null($lat2) || is_null($lon2)) {
            return null;
        }

        $earthRadius = 6371; // in kilometers

        $latFrom = deg2rad((float) $lat1);
        $lonFrom = deg2rad((float) $lon1);
        $latTo = deg2rad((float) $lat2);
        $lonTo = deg2rad((float) $lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return round($angle * $earthRadius, 1);
    }
}
