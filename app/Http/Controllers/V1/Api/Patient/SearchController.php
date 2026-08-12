<?php

namespace App\Http\Controllers\V1\Api\Patient;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Partner;
use App\Models\ProfessionalSpeciality;
use App\Models\RecentSearch;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = $request->query('query');
        $specialityId = $request->query('speciality_id');
        $userLatitude = $request->query('latitude');
        $userLongitude = $request->query('longitude');

        // 1. Fetch Popular Specialities
        $popularSpecialities = [];
        $specialties = ProfessionalSpeciality::orderBy('id', 'asc')
            ->limit(8)
            ->get();

        foreach ($specialties as $specialty) {
            $count = Partner::where('partner_type', 'professional')
                ->where('professional_speciality_code', $specialty->code)
                ->where('is_available', true)
                ->count();

            $popularSpecialities[] = [
                'id' => $specialty->id,
                'label' => $specialty->ar ?? $specialty->en ?? $specialty->code,
                'slug' => $specialty->code,
                'professionals_count' => $count,
            ];
        }

        // 2. Fetch Recent Searches
        $recentSearches = [];
        $user = $request->user();
        if ($user) {
            $searches = RecentSearch::with('speciality')
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            foreach ($searches as $search) {
                $recentSearches[] = [
                    'id' => $search->id,
                    'label' => $search->label,
                    'city' => $search->city,
                    'speciality' => $search->speciality ? [
                        'id' => $search->speciality->id,
                        'label' => $search->speciality->ar ?? $search->speciality->en ?? $search->speciality->code,
                        'slug' => $search->speciality->code,
                    ] : null,
                    'created_at' => $search->created_at?->toIso8601String(),
                ];
            }
        }

        // 3. Perform Global Search (Partners)
        $results = [];

        if ($query || $specialityId) {
            $partnerQuery = Partner::with(['user', 'speciality', 'catalog', 'wilaya'])
                ->where('is_active', true)
                ->where('is_available', true);

            if ($specialityId) {
                $partnerQuery->whereHas('speciality', function ($q) use ($specialityId) {
                    $q->where('id', $specialityId);
                });
            }

            if ($query) {
                $partnerQuery->where(function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                        ->orWhere('city', 'like', "%{$query}%")
                        ->orWhere('address', 'like', "%{$query}%")
                        ->orWhere('bio', 'like', "%{$query}%")
                        ->orWhereHas('user', function ($uq) use ($query) {
                            $uq->where('full_name', 'like', "%{$query}%")
                                ->orWhere('name', 'like', "%{$query}%");
                        })->orWhereHas('speciality', function ($sq) use ($query) {
                            $sq->where('ar', 'like', "%{$query}%")
                                ->orWhere('en', 'like', "%{$query}%");
                        })->orWhereHas('catalog', function ($cq) use ($query) {
                            $cq->where('ar', 'like', "%{$query}%")
                                ->orWhere('en', 'like', "%{$query}%");
                        });
                });
            }

            $partners = $partnerQuery->get();

            foreach ($partners as $partner) {
                $distance = $this->calculateDistance($userLatitude, $userLongitude, $partner->latitude, $partner->longitude);

                $partnerName = $partner->name ?? $partner->user?->full_name ?? $partner->user?->name ?? 'شريك';
                if ($partner->partner_type === 'professional' && ! str_starts_with($partnerName, 'د.')) {
                    $partnerName = 'د. '.$partnerName;
                }

                $subtitle = 'أخصائي';
                if ($partner->partner_type === 'center') {
                    $subtitle = $partner->catalog?->ar ?? $partner->catalog?->en ?? 'مركز طبي';
                } elseif ($partner->partner_type === 'pharmacist') {
                    $subtitle = 'صيدلية';
                } else {
                    $subtitle = $partner->speciality?->ar ?? $partner->speciality?->en ?? 'أخصائي';
                }

                $results[] = [
                    'type' => $partner->partner_type === 'pharmacist' ? 'pharmacy' : $partner->partner_type,
                    'id' => $partner->id,
                    'name' => $partnerName,
                    'subtitle' => $subtitle,
                    'city' => $partner->city ?? $partner->address,
                    'rating' => 4.8,
                    'reviews_count' => 120,
                    'image' => $partner->user?->image_url,
                    'distance' => $distance,
                    'distance_text' => $distance !== null ? "{$distance} كم" : null,
                ];
            }
        }

        // Sort search results by distance if user location is available
        if ($userLatitude !== null && $userLongitude !== null) {
            usort($results, function ($a, $b) {
                $distA = $a['distance'] ?? INF;
                $distB = $b['distance'] ?? INF;

                return $distA <=> $distB;
            });
        }

        $page = max((int) $request->query('page', 1), 1);
        $perPage = max((int) $request->query('per_page', 10), 1);
        $total = count($results);
        $offset = ($page - 1) * $perPage;
        $pagedResults = array_slice($results, $offset, $perPage);
        $nextPage = ($offset + $perPage < $total) ? $page + 1 : null;

        return response()->json([
            'search_placeholder' => 'ابحث عن طبيب، تخصص، مركز أو صيدلية...',
            'popular_specialities' => $popularSpecialities,
            'recent_searches' => $recentSearches,
            'results' => $pagedResults,
            'current_page' => $page,
            'next_page' => $nextPage,
        ]);
    }

    public function feed(Request $request): JsonResponse
    {
        $user = $request->user();
        $page = max((int) $request->query('page', 1), 1);
        $perPage = max((int) $request->query('per_page', 10), 1);
        $userLatitude = $request->query('latitude');
        $userLongitude = $request->query('longitude');

        // Determine if the user is a patient and get their patient ID
        $patientId = null;
        if ($user && $user->user_role_code === 'patient') {
            $patient = $user->patient;
            $patientId = $patient ? $patient->id : null;
        }

        $feedItems = [];

        // 1. Bookings (if patient)
        if ($patientId) {
            $bookings = Booking::where('patient_id', $patientId)
                ->orderBy('created_at', 'desc')
                ->limit(100)
                ->get();

            foreach ($bookings as $booking) {
                $bookableName = 'N/A';
                $bookableLat = null;
                $bookableLon = null;

                if ($booking->bookable_type === Partner::class || str_contains($booking->bookable_type ?? '', 'Partner')) {
                    $partner = Partner::find($booking->bookable_id);
                    if ($partner) {
                        $bookableName = $partner->name ?? $partner->user?->full_name ?? $partner->user?->name ?? 'شريك';
                        if ($partner->partner_type === 'professional' && ! str_starts_with($bookableName, 'د.')) {
                            $bookableName = 'د. '.$bookableName;
                        }
                        $bookableLat = $partner->latitude;
                        $bookableLon = $partner->longitude;
                    }
                }

                $distance = $this->calculateDistance($userLatitude, $userLongitude, $bookableLat, $bookableLon);

                $feedItems[] = [
                    'feed_type' => 'booking',
                    'id' => $booking->id,
                    'reference' => $booking->reference,
                    'date' => $booking->booking_date ? (is_string($booking->booking_date) ? $booking->booking_date : $booking->booking_date->format('Y-m-d')) : '',
                    'time' => $booking->booking_time ? Carbon::parse($booking->booking_time)->format('H:i') : '',
                    'status' => $booking->status?->ar ?? $booking->status?->en ?? $booking->status_code,
                    'type' => $booking->is_center ? 'center' : 'professional',
                    'bookable_name' => $bookableName,
                    'created_at' => $booking->created_at,
                    'distance' => $distance,
                    'distance_text' => $distance !== null ? "{$distance} كم" : null,
                ];
            }
        }

        // 2. Partners Feed
        $partners = Partner::with(['user', 'speciality', 'catalog'])
            ->where('is_active', true)
            ->where('is_available', true)
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();

        foreach ($partners as $partner) {
            $distance = $this->calculateDistance($userLatitude, $userLongitude, $partner->latitude, $partner->longitude);
            $name = $partner->name ?? $partner->user?->full_name ?? $partner->user?->name ?? 'شريك';
            if ($partner->partner_type === 'professional' && ! str_starts_with($name, 'د.')) {
                $name = 'د. '.$name;
            }

            $feedType = $partner->partner_type === 'pharmacist' ? 'pharmacy' : $partner->partner_type;

            $feedItems[] = [
                'feed_type' => $feedType,
                'id' => $partner->id,
                'name' => $name,
                'speciality' => $partner->speciality?->ar ?? $partner->catalog?->ar ?? $partner->speciality?->en ?? $partner->partner_type,
                'city' => $partner->city ?? $partner->address,
                'image' => $partner->user?->image_url,
                'created_at' => $partner->created_at,
                'distance' => $distance,
                'distance_text' => $distance !== null ? "{$distance} كم" : null,
            ];
        }

        // Sort feed items: prioritize distance if user location is available, otherwise created_at desc
        usort($feedItems, function ($a, $b) use ($userLatitude, $userLongitude) {
            if ($userLatitude !== null && $userLongitude !== null) {
                $distA = $a['distance'] ?? INF;
                $distB = $b['distance'] ?? INF;
                if ($distA != $distB) {
                    return $distA <=> $distB;
                }
            }
            $aTime = $a['created_at'] ? ($a['created_at'] instanceof Carbon ? $a['created_at']->timestamp : strtotime((string) $a['created_at'])) : 0;
            $bTime = $b['created_at'] ? ($b['created_at'] instanceof Carbon ? $b['created_at']->timestamp : strtotime((string) $b['created_at'])) : 0;

            return $bTime <=> $aTime;
        });

        // Format created_at to ISO string
        foreach ($feedItems as &$item) {
            if ($item['created_at'] instanceof Carbon) {
                $item['created_at'] = $item['created_at']->toIso8601String();
            } elseif (is_string($item['created_at'])) {
                $item['created_at'] = date(DATE_ISO8601, strtotime($item['created_at']));
            }
        }
        unset($item);

        // Paginate in-memory
        $total = count($feedItems);
        $offset = ($page - 1) * $perPage;
        $pagedItems = array_slice($feedItems, $offset, $perPage);
        $nextPage = ($offset + $perPage < $total) ? $page + 1 : null;

        return response()->json([
            'results' => $pagedItems,
            'current_page' => $page,
            'next_page' => $nextPage,
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
