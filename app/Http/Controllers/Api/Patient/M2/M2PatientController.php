<?php

namespace App\Http\Controllers\Api\Patient\M2;

use App\Http\Controllers\Controller;
use App\Models\RecentSearch;
use App\Models\Speciality;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class M2PatientController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $placeholder = "ابحث عن طبيب، تخصص أو مركز...";

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

        return response()->json([
            'placeholder' => $placeholder,
            'popular_specialities' => $popularSpecialities,
            'recent_searches' => $recentSearches,
        ]);
    }
}
