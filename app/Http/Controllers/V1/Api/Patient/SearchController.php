<?php

namespace App\Http\Controllers\V1\Api\Patient;

use App\Http\Controllers\Controller;
use App\Models\Center;
use App\Models\Professional;
use App\Models\Pharmacy;
use App\Models\RecentSearch;
use App\Models\ProfessionalSpeciality;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
  public function index(Request $request): JsonResponse
  {
    $query = $request->query('query');
    $specialityId = $request->query('speciality_id');

    // 1. Fetch Popular Specialities
    $popularSpecialities = [];
    $specialties = ProfessionalSpeciality::withCount('professionals')
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
            'label' => $search->speciality->ar ?? $search->speciality->en ?? $search->speciality->code,
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
      $profQuery = Professional::with(['user', 'specialty'])->where('is_available', true);

      if ($specialityId) {
        $profQuery->whereHas('specialty', function ($q) use ($specialityId) {
          $q->where('id', $specialityId);
        });
      }

      if ($query) {
        $profQuery->where(function ($q) use ($query) {
          $q->whereHas('user', function ($uq) use ($query) {
            $uq->where('full_name', 'like', "%{$query}%")
              ->orWhere('name', 'like', "%{$query}%");
          })->orWhereHas('specialty', function ($sq) use ($query) {
            $sq->where('ar', 'like', "%{$query}%")
              ->orWhere('en', 'like', "%{$query}%");
          })->orWhere('profession_code', 'like', "%{$query}%");
        });
      }

      $professionals = $profQuery->limit(10)->get();
      foreach ($professionals as $prof) {
        $results[] = [
          'type' => 'professional',
          'id' => $prof->id,
          'name' => ($prof->profession_code === 'doctor' ? 'د. ' : '') . ($prof->user->full_name ?? $prof->user->name ?? ''),
          'subtitle' => $prof->specialty?->ar ?? $prof->specialty?->en ?? ucfirst($prof->profession_code),
          'city' => $prof->city,
          'rating' => 4.8,
          'reviews_count' => 120,
          'image' => null,
        ];
      }

      // Search Centers
      if (!$specialityId) {
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

      // Search Pharmacies
      if (!$specialityId) {
        $pharmacyQuery = Pharmacy::with(['user'])->where('is_available', true);

        if ($query) {
          $pharmacyQuery->where(function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
              ->orWhere('location', 'like', "%{$query}%")
              ->orWhere('bio', 'like', "%{$query}%");
          });
        }

        $pharmacies = $pharmacyQuery->limit(10)->get();
        foreach ($pharmacies as $pharmacy) {
          $results[] = [
            'type' => 'pharmacy',
            'id' => $pharmacy->id,
            'name' => $pharmacy->name ?? $pharmacy->user?->name ?? 'صيدلية',
            'subtitle' => 'صيدلية',
            'city' => $pharmacy->location,
            'rating' => 4.7,
            'reviews_count' => 84,
            'image' => null,
          ];
        }
      }
    }

    return response()->json([
      'search_placeholder' => 'ابحث عن طبيب، تخصص، مركز أو صيدلية...',
      'popular_specialities' => $popularSpecialities,
      'recent_searches' => $recentSearches,
      'results' => $results,
    ]);
  }
}
