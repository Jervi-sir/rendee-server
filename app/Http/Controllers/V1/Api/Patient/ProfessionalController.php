<?php

namespace App\Http\Controllers\V1\Api\Patient;

use App\Http\Controllers\Controller;
use App\Models\Professional;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfessionalController extends Controller
{
    /**
     * Display a listing of professionals with optional filtering.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Professional::with(['user', 'profession', 'specialty', 'wilaya']);

        if ($request->has('profession_code')) {
            $query->where('profession_code', $request->query('profession_code'));
        }

        if ($request->has('speciality_code') || $request->has('specialty_code') || $request->has('professional_speciality_code')) {
            $specialityCode = $request->query('speciality_code')
                ?? $request->query('specialty_code')
                ?? $request->query('professional_speciality_code');
            $query->where('professional_speciality_code', $specialityCode);
        }

        if ($request->has('wilaya_code')) {
            $query->where('wilaya_code', $request->query('wilaya_code'));
        }

        if ($request->has('city')) {
            $query->where('city', 'like', '%'.$request->query('city').'%');
        }

        $professionals = $query->where('is_available', true)->get()->map(function ($professional) {
            return $professional->formatForPatient(false);
        });

        return response()->json([
            'professionals' => $professionals,
        ]);
    }

    /**
     * Display detailed profile for a specific professional.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $professional = Professional::with([
            'user',
            'profession',
            'specialty',
            'wilaya',
            'schedules',
            'contacts',
            'services.serviceCatalog',
        ])->find($id);

        if (! $professional) {
            return response()->json([
                'message' => 'Professional not found.',
            ], 404);
        }

        return response()->json([
            'professional' => $professional->formatForPatient(true),
        ]);
    }
}
