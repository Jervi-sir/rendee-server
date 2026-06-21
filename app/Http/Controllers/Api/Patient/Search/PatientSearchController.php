<?php

namespace App\Http\Controllers\Api\Patient\Search;

use App\Http\Controllers\Controller;
use App\Models\Center;
use App\Models\Doctor;
use App\Models\RecentSearch;
use App\Models\Speciality;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PatientSearchController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = $request->query('query');
        $specialityId = $request->query('speciality_id');

        $popularSpecialities = [];
        $specialties = Speciality::withCount('doctors')
            ->orderBy('doctors_count', 'desc')
            ->limit(8)
            ->get();

        foreach ($specialties as $specialty) {
            $popularSpecialities[] = [
                'id' => $specialty->id,
                'label' => $specialty->ar ?? $specialty->en ?? $specialty->code,
                'slug' => $specialty->code,
                'doctors_count' => $specialty->doctors_count,
            ];
        }

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

        // Perform Search Results
        $results = [];

        if ($query || $specialityId) {
            // Search Doctors
            $doctorQuery = Doctor::with(['user', 'specialty'])->where('is_available', true);

            if ($specialityId) {
                $doctorQuery->whereHas('specialty', function($q) use ($specialityId) {
                    $q->where('id', $specialityId);
                });
            }

            if ($query) {
                $doctorQuery->where(function($q) use ($query) {
                    $q->whereHas('user', function($uq) use ($query) {
                        $uq->where('full_name', 'like', "%{$query}%")
                           ->orWhere('name', 'like', "%{$query}%");
                    })->orWhereHas('specialty', function($sq) use ($query) {
                        $sq->where('ar', 'like', "%{$query}%")
                           ->orWhere('en', 'like', "%{$query}%");
                    });
                });
            }

            $doctors = $doctorQuery->limit(10)->get();
            foreach ($doctors as $doctor) {
                $results[] = [
                    'type' => 'doctor',
                    'id' => $doctor->id,
                    'name' => 'د. ' . ($doctor->user->full_name ?? $doctor->user->name ?? ''),
                    'subtitle' => $doctor->specialty?->ar ?? $doctor->specialty?->en ?? 'طبيب عام',
                    'city' => $doctor->city,
                    'rating' => 4.8,
                    'reviews_count' => 156,
                    'image' => null,
                ];
            }

            // Search Centers
            if (!$specialityId) {
                $centerQuery = Center::with(['user', 'catalog'])->where('is_active', true);
                if ($query) {
                    $centerQuery->where(function($q) use ($query) {
                        $q->where('name', 'like', "%{$query}%")
                           ->orWhereHas('catalog', function($cq) use ($query) {
                               $cq->where('ar', 'like', "%{$query}%")
                                  ->orWhere('en', 'like', "%{$query}%");
                           });
                    });
                }

                $centers = $centerQuery->limit(10)->get();
                foreach ($centers as $center) {
                    $results[] = [
                        'type' => 'center',
                        'id' => $center->id,
                        'name' => $center->name ?? $center->user?->name ?? 'مركز طبي',
                        'subtitle' => $center->catalog?->ar ?? $center->catalog?->en ?? 'مركز طبي',
                        'city' => $center->city,
                        'rating' => 4.9,
                        'reviews_count' => 127,
                        'image' => null,
                    ];
                }
            }
        }

        return response()->json([
            'search_placeholder' => 'ابحث عن طبيب، تخصص أو مركز...',
            'popular_specialities' => $popularSpecialities,
            'recent_searches' => $recentSearches,
            'results' => $results,
        ]);
    }
}
