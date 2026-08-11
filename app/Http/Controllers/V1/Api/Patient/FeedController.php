<?php

namespace App\Http\Controllers\V1\Api\Patient;

use App\Http\Controllers\Controller;
use App\Models\Center;
use App\Models\LikeItem;
use App\Models\Pharmacy;
use App\Models\Professional;
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
        $userType = $request->query('user_type'); // 'doctor' | 'professional' | 'center' | 'pharmacy'
        $onDuty = $request->query('on_duty') ? filter_var($request->query('on_duty'), FILTER_VALIDATE_BOOLEAN) : null;
        $minRating = $request->query('min_rating') ? (float) $request->query('min_rating') : null;

        $results = [];

        // 1. Fetch Professionals
        if (! $userType || in_array($userType, ['professional', 'doctor'])) {
            $profQuery = Professional::with(['user', 'speciality', 'wilaya'])->where('is_available', true);

            if ($query) {
                $profQuery->where(function ($q) use ($query) {
                    $q->whereHas('user', function ($uq) use ($query) {
                        $uq->where('full_name', 'like', "%{$query}%")
                            ->orWhere('name', 'like', "%{$query}%");
                    })->orWhereHas('speciality', function ($sq) use ($query) {
                        $sq->where('ar', 'like', "%{$query}%")
                            ->orWhere('en', 'like', "%{$query}%");
                    })->orWhere('profession_code', 'like', "%{$query}%");
                });
            }

            if ($userWilaya && $userWilaya !== 'all') {
                $profQuery->where(function ($q) use ($userWilaya) {
                    $q->where('wilaya_code', $userWilaya)
                        ->orWhere('city', 'like', "%{$userWilaya}%")
                        ->orWhereHas('wilaya', function ($wq) use ($userWilaya) {
                            $wq->where('code', $userWilaya)
                                ->orWhere('number', $userWilaya)
                                ->orWhere('ar', 'like', "%{$userWilaya}%")
                                ->orWhere('en', 'like', "%{$userWilaya}%");
                        });
                });
            }

            foreach ($profQuery->get() as $prof) {
                $distance = $this->calculateDistance($userLatitude, $userLongitude, $prof->latitude, $prof->longitude);

                if ($nearMe && $distance === null) {
                    continue;
                }

                $fullName = $prof->user?->full_name ?? $prof->user?->name ?? '';
                if ($prof->profession_code === 'doctor' && ! str_starts_with($fullName, 'د.')) {
                    $fullName = 'د. '.$fullName;
                }

                $rating = isset($prof->rating) ? (float) $prof->rating : 4.8;
                $reviewsCount = isset($prof->reviews_count) ? (int) $prof->reviews_count : 0;
                $isOnDuty = (bool) ($prof->is_on_duty ?? false);

                if ($onDuty && ! $isOnDuty) {
                    continue;
                }
                if ($minRating !== null && $rating < $minRating) {
                    continue;
                }

                $isLiked = isset($userLikedMap[Professional::class.':'.$prof->id]);

                $results[] = [
                    'id' => $prof->id,
                    'name' => $fullName,
                    'fullname' => $fullName,
                    'type' => 'doctor',
                    'user_type' => 'professional',
                    'typeLabel' => $prof->profession_code === 'doctor' ? 'طبيب أخصائي' : 'أخصائي',
                    'type_label' => $prof->profession_code === 'doctor' ? 'طبيب أخصائي' : 'أخصائي',
                    'specialty' => $prof->speciality?->ar ?? $prof->speciality?->en ?? ucfirst((string) $prof->profession_code),
                    'speciality' => $prof->speciality?->ar ?? $prof->speciality?->en ?? ucfirst((string) $prof->profession_code),
                    'distance' => $distance,
                    'distance_text' => $distance !== null ? "{$distance} كم" : null,
                    'rating' => $rating,
                    'reviews_count' => $reviewsCount,
                    'isOnDuty' => $isOnDuty,
                    'is_on_duty' => $isOnDuty,
                    'is_liked' => $isLiked,
                    'is_favorite' => $isLiked,
                    'wilaya' => $prof->wilaya?->ar ?? $prof->wilaya?->en ?? $prof->city,
                    'address' => $prof->address ?? $prof->city,
                    'location' => [
                        'wilaya_name' => $prof->wilaya?->ar ?? $prof->wilaya?->en ?? $prof->city,
                        'wilaya_number' => $prof->wilaya?->number ? (int) $prof->wilaya->number : null,
                        'address' => $prof->address ?? $prof->city,
                    ],
                    'images' => $prof->user?->image_url ? [$prof->user->image_url] : [],
                    'created_at' => $prof->created_at?->toIso8601String(),
                ];
            }
        }

        // 2. Fetch Centers
        if (! $userType || $userType === 'center') {
            $centerQuery = Center::with(['user', 'catalog', 'wilaya'])->where('is_active', true);

            if ($query) {
                $centerQuery->where(function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                        ->orWhere('description', 'like', "%{$query}%")
                        ->orWhereHas('catalog', function ($cq) use ($query) {
                            $cq->where('ar', 'like', "%{$query}%")
                                ->orWhere('en', 'like', "%{$query}%");
                        });
                });
            }

            if ($userWilaya && $userWilaya !== 'all') {
                $centerQuery->where(function ($q) use ($userWilaya) {
                    $q->where('city', 'like', "%{$userWilaya}%")
                        ->orWhereHas('wilaya', function ($wq) use ($userWilaya) {
                            $wq->where('code', $userWilaya)
                                ->orWhere('number', $userWilaya)
                                ->orWhere('ar', 'like', "%{$userWilaya}%")
                                ->orWhere('en', 'like', "%{$userWilaya}%");
                        });
                });
            }

            foreach ($centerQuery->get() as $center) {
                $distance = $this->calculateDistance($userLatitude, $userLongitude, $center->latitude, $center->longitude);

                if ($nearMe && $distance === null) {
                    continue;
                }

                $rating = isset($center->rating) ? (float) $center->rating : 4.7;
                $reviewsCount = isset($center->reviews_count) ? (int) $center->reviews_count : 0;
                $isOnDuty = (bool) ($center->is_on_duty ?? false);

                if ($onDuty && ! $isOnDuty) {
                    continue;
                }
                if ($minRating !== null && $rating < $minRating) {
                    continue;
                }

                $centerName = $center->name ?? $center->user?->full_name ?? $center->user?->name ?? 'مركز طبي';
                $isLiked = isset($userLikedMap[Center::class.':'.$center->id]);

                $results[] = [
                    'id' => $center->id,
                    'name' => $centerName,
                    'fullname' => $centerName,
                    'type' => 'center',
                    'user_type' => 'center',
                    'typeLabel' => 'مركز طبي متكامل',
                    'type_label' => 'مركز طبي متكامل',
                    'specialty' => $center->catalog?->ar ?? $center->catalog?->en ?? 'مركز طبي',
                    'speciality' => $center->catalog?->ar ?? $center->catalog?->en ?? 'مركز طبي',
                    'distance' => $distance,
                    'distance_text' => $distance !== null ? "{$distance} كم" : null,
                    'rating' => $rating,
                    'reviews_count' => $reviewsCount,
                    'isOnDuty' => $isOnDuty,
                    'is_on_duty' => $isOnDuty,
                    'is_liked' => $isLiked,
                    'is_favorite' => $isLiked,
                    'wilaya' => $center->wilaya?->ar ?? $center->wilaya?->en ?? $center->city,
                    'address' => $center->address ?? $center->city,
                    'location' => [
                        'wilaya_name' => $center->wilaya?->ar ?? $center->wilaya?->en ?? $center->city,
                        'wilaya_number' => $center->wilaya?->number ? (int) $center->wilaya->number : null,
                        'address' => $center->address ?? $center->city,
                    ],
                    'images' => $center->user?->image_url ? [$center->user->image_url] : [],
                    'created_at' => $center->created_at?->toIso8601String(),
                ];
            }
        }

        // 3. Fetch Pharmacies
        if (! $userType || $userType === 'pharmacy') {
            $pharmacyQuery = Pharmacy::with(['user', 'wilaya'])->where('is_available', true);

            if ($query) {
                $pharmacyQuery->where(function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                        ->orWhere('address', 'like', "%{$query}%")
                        ->orWhere('city', 'like', "%{$query}%")
                        ->orWhere('bio', 'like', "%{$query}%");
                });
            }

            if ($userWilaya && $userWilaya !== 'all') {
                $pharmacyQuery->where(function ($q) use ($userWilaya) {
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

            foreach ($pharmacyQuery->get() as $pharmacy) {
                $distance = $this->calculateDistance($userLatitude, $userLongitude, $pharmacy->latitude, $pharmacy->longitude);

                if ($nearMe && $distance === null) {
                    continue;
                }

                $rating = isset($pharmacy->rating) ? (float) $pharmacy->rating : 4.9;
                $reviewsCount = isset($pharmacy->reviews_count) ? (int) $pharmacy->reviews_count : 0;
                $isOnDuty = (bool) ($pharmacy->is_on_duty ?? false);

                if ($onDuty && ! $isOnDuty) {
                    continue;
                }
                if ($minRating !== null && $rating < $minRating) {
                    continue;
                }

                $pharmacyName = $pharmacy->name ?? $pharmacy->user?->full_name ?? $pharmacy->user?->name ?? 'صيدلية';
                $isLiked = isset($userLikedMap[Pharmacy::class.':'.$pharmacy->id]);

                $results[] = [
                    'id' => $pharmacy->id,
                    'name' => $pharmacyName,
                    'fullname' => $pharmacyName,
                    'type' => 'pharmacy',
                    'user_type' => 'pharmacy',
                    'typeLabel' => $isOnDuty ? 'صيدلية مناوبة' : 'صيدلية',
                    'type_label' => $isOnDuty ? 'صيدلية مناوبة' : 'صيدلية',
                    'specialty' => 'صيدلية',
                    'speciality' => 'صيدلية',
                    'distance' => $distance,
                    'distance_text' => $distance !== null ? "{$distance} كم" : null,
                    'rating' => $rating,
                    'reviews_count' => $reviewsCount,
                    'isOnDuty' => $isOnDuty,
                    'is_on_duty' => $isOnDuty,
                    'is_liked' => $isLiked,
                    'is_favorite' => $isLiked,
                    'wilaya' => $pharmacy->wilaya?->ar ?? $pharmacy->wilaya?->en ?? $pharmacy->location,
                    'address' => $pharmacy->location,
                    'location' => [
                        'wilaya_name' => $pharmacy->wilaya?->ar ?? $pharmacy->wilaya?->en ?? $pharmacy->location,
                        'wilaya_number' => $pharmacy->wilaya?->number ? (int) $pharmacy->wilaya->number : null,
                        'address' => $pharmacy->location,
                    ],
                    'images' => $pharmacy->user?->image_url ? [$pharmacy->user->image_url] : [],
                    'created_at' => $pharmacy->created_at?->toIso8601String(),
                ];
            }
        }

        // 4. Sort Results (by distance if coordinates provided, else created_at desc)
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

        // 5. Pagination
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
