<?php

namespace App\Http\Controllers\V1\Api\Patient;

use App\Http\Controllers\Controller;
use App\Models\Center;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CenterController extends Controller
{
    /**
     * Display a listing of medical centers/laboratories.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Center::with(['catalog', 'user']);

        if ($request->has('center_catalog_code')) {
            $query->where('center_catalog_code', $request->query('center_catalog_code'));
        }

        if ($request->has('city')) {
            $query->where('city', 'like', '%' . $request->query('city') . '%');
        }

        $centers = $query->where('is_active', true)->get()->map(function ($center) {
            return $center->formatForPatient(false);
        });

        return response()->json([
            'centers' => $centers,
        ]);
    }

    /**
     * Display detailed profile for a specific center.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $center = Center::with([
            'catalog',
            'user',
            'contacts',
            'workingHours',
            'services.serviceCatalog'
        ])->find($id);

        if (!$center) {
            return response()->json([
                'message' => 'Center not found.',
            ], 404);
        }

        return response()->json([
            'center' => $center->formatForPatient(true),
        ]);
    }
}
