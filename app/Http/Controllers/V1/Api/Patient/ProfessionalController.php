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
    $query = Professional::with(['user', 'specialty']);

    if ($request->has('profession_code')) {
      $query->where('profession_code', $request->query('profession_code'));
    }

    if ($request->has('speciality_code')) {
      $query->where('speciality_code', $request->query('speciality_code'));
    }

    if ($request->has('city')) {
      $query->where('city', 'like', '%' . $request->query('city') . '%');
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
      'specialty',
      'schedules',
      'contacts',
      'services.serviceCatalog'
    ])->find($id);

    if (!$professional) {
      return response()->json([
        'message' => 'Professional not found.',
      ], 404);
    }

    return response()->json([
      'professional' => $professional->formatForPatient(true),
    ]);
  }
}
