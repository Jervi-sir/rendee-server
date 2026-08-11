<?php

namespace App\Http\Controllers\V1\Api\Patient;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Center;
use App\Models\Pharmacy;
use App\Models\Professional;
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
        $specialties = ProfessionalSpeciality::withCount([
            'professionals' => function ($query) {
                $query->where('is_available', true);
            },
        ])
            ->orderBy('professionals_count', 'desc')
            ->limit(8)
            ->get();

        foreach ($specialties as $specialty) {
            $popularSpecialities[] = [
                'id' => $specialty->id,
                'label' => $specialty->ar ?? $specialty->en ?? $specialty->code,
                'slug' => $specialty->code,
                'professionals_count' => $specialty->professionals_count,
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
                        'label' => $search->speciality->ar ?? $specialty->en ?? $specialty->code,
                        'slug' => $search->speciality->code,
                    ] : null,
                    'created_at' => $search->created_at?->toIso8601String(),
                ];
            }
        }

        // 3. Perform Global Search (excluding patients)
        $results = [];

        if ($query || $specialityId) {

            // Search Professionals
            $profQuery = Professional::with(['user', 'speciality'])->where('is_available', true);

            if ($specialityId) {
                $profQuery->whereHas('speciality', function ($q) use ($specialityId) {
                    $q->where('id', $specialityId);
                });
            }

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
                $professionals = $profQuery->get();
                foreach ($professionals as $prof) {
                    $distance = $this->calculateDistance($userLatitude, $userLongitude, $prof->latitude, $prof->longitude);
                    $results[] = [
                        'type' => 'professional',
                        'id' => $prof->id,
                        'name' => ($prof->profession_code === 'doctor' ? 'د. ' : '').($prof->user->full_name ?? $prof->user->name ?? ''),
                        'subtitle' => $prof->speciality?->ar ?? $prof->speciality?->en ?? ucfirst($prof->profession_code),
                        'city' => $prof->city,
                        'rating' => 4.8,
                        'reviews_count' => 120,
                        'image' => null,
                        'distance' => $distance,
                        'distance_text' => $distance !== null ? "{$distance} كم" : null,
                    ];
                }
            }

            // Search Centers
            if (! $specialityId) {
                $centerQuery = Center::with(['user', 'catalog'])->where('is_active', true);

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

                $centers = $centerQuery->get();
                foreach ($centers as $center) {
                    $distance = $this->calculateDistance($userLatitude, $userLongitude, $center->latitude, $center->longitude);
                    $results[] = [
                        'type' => 'center',
                        'id' => $center->id,
                        'name' => $center->name ?? $center->user?->name ?? 'مركز طبي',
                        'subtitle' => $center->catalog?->ar ?? $center->catalog?->en ?? 'مركز طبي',
                        'city' => $center->city,
                        'rating' => 4.9,
                        'reviews_count' => 127,
                        'image' => null,
                        'distance' => $distance,
                        'distance_text' => $distance !== null ? "{$distance} كم" : null,
                    ];
                }
            }

            // Search Pharmacies
            if (! $specialityId) {
                $pharmacyQuery = Pharmacy::with(['user'])->where('is_available', true);

                if ($query) {
                    $pharmacyQuery->where(function ($q) use ($query) {
                        $q->where('name', 'like', "%{$query}%")
                            ->orWhere('location', 'like', "%{$query}%")
                            ->orWhere('bio', 'like', "%{$query}%");
                    });
                }

                $pharmacies = $pharmacyQuery->get();
                foreach ($pharmacies as $pharmacy) {
                    $distance = $this->calculateDistance($userLatitude, $userLongitude, $pharmacy->latitude, $pharmacy->longitude);
                    $results[] = [
                        'type' => 'pharmacy',
                        'id' => $pharmacy->id,
                        'name' => $pharmacy->name ?? $pharmacy->user?->name ?? 'صيدلية',
                        'subtitle' => 'صيدلية',
                        'city' => $pharmacy->location,
                        'rating' => 4.7,
                        'reviews_count' => 84,
                        'image' => null,
                        'distance' => $distance,
                        'distance_text' => $distance !== null ? "{$distance} كم" : null,
                    ];
                }
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
                $bookableType = $booking->bookable_type;
                $bookableLat = null;
                $bookableLon = null;

                if ($bookableType === Professional::class) {
                    $prof = Professional::find($booking->bookable_id);
                    if ($prof) {
                        $bookableName = ($prof->profession_code === 'doctor' ? 'د. ' : '').($prof->user->full_name ?? $prof->user->name ?? '');
                        $bookableLat = $prof->latitude;
                        $bookableLon = $prof->longitude;
                    }
                } elseif ($bookableType === Center::class) {
                    $center = Center::find($booking->bookable_id);
                    if ($center) {
                        $bookableName = $center->name ?? $center->user?->name ?? 'مركز طبي';
                        $bookableLat = $center->latitude;
                        $bookableLon = $center->longitude;
                    }
                }

                $distance = $this->calculateDistance($userLatitude, $userLongitude, $bookableLat, $bookableLon);

                $feedItems[] = [
                    'feed_type' => 'booking',
                    'id' => $booking->id,
                    'reference' => $booking->reference,
                    'date' => $booking->booking_date,
                    'time' => $booking->booking_time,
                    'status' => $booking->status?->ar ?? $booking->status?->en ?? $booking->status_code,
                    'type' => $booking->is_center ? 'center' : 'professional',
                    'bookable_name' => $bookableName,
                    'created_at' => $booking->created_at,
                    'distance' => $distance,
                    'distance_text' => $distance !== null ? "{$distance} كم" : null,
                ];
            }
        }

        // 2. Professionals
        $professionals = Professional::with(['user', 'speciality'])
            ->where('is_available', true)
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();

        foreach ($professionals as $prof) {
            $distance = $this->calculateDistance($userLatitude, $userLongitude, $prof->latitude, $prof->longitude);
            $feedItems[] = [
                'feed_type' => 'professional',
                'id' => $prof->id,
                'name' => ($prof->profession_code === 'doctor' ? 'د. ' : '').($prof->user->full_name ?? $prof->user->name ?? ''),
                'speciality' => $prof->speciality?->ar ?? $prof->speciality?->en ?? $prof->profession_code,
                'city' => $prof->city,
                'image' => null,
                'created_at' => $prof->created_at,
                'distance' => $distance,
                'distance_text' => $distance !== null ? "{$distance} كم" : null,
            ];
        }

        // 3. Centers
        $centers = Center::with(['user', 'catalog'])
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();

        foreach ($centers as $center) {
            $distance = $this->calculateDistance($userLatitude, $userLongitude, $center->latitude, $center->longitude);
            $feedItems[] = [
                'feed_type' => 'center',
                'id' => $center->id,
                'name' => $center->name ?? $center->user?->name ?? 'مركز طبي',
                'type' => $center->catalog?->ar ?? $center->catalog?->en ?? 'مركز طبي',
                'city' => $center->city,
                'image' => null,
                'created_at' => $center->created_at,
                'distance' => $distance,
                'distance_text' => $distance !== null ? "{$distance} كم" : null,
            ];
        }

        // 4. Pharmacies
        $pharmacies = Pharmacy::with(['user'])
            ->where('is_available', true)
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();

        foreach ($pharmacies as $pharm) {
            $distance = $this->calculateDistance($userLatitude, $userLongitude, $pharm->latitude, $pharm->longitude);
            $feedItems[] = [
                'feed_type' => 'pharmacy',
                'id' => $pharm->id,
                'name' => $pharm->name ?? $pharm->user?->name ?? 'صيدلية',
                'city' => $pharm->location,
                'image' => null,
                'created_at' => $pharm->created_at,
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

        // Format created_at to ISO string and clean up reference before response
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
