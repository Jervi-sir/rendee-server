<?php

namespace App\Http\Controllers\V1\Api\Patient;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CenterController extends Controller
{
    /**
     * Display a listing of medical centers/laboratories.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Partner::with(['catalog', 'user', 'wilaya'])
            ->whereIn('partner_type', ['CENTER', 'center']);

        if ($request->has('center_catalog_code')) {
            $query->where('center_catalog_code', $request->query('center_catalog_code'));
        }

        if ($request->has('wilaya_code')) {
            $query->where('wilaya_code', $request->query('wilaya_code'));
        }

        if ($request->has('city')) {
            $query->where('city', 'like', '%'.$request->query('city').'%');
        }

        $centers = $query->where('is_active', true)->get()->map(function ($partner) {
            return $partner->formatForPatient(false);
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
        $partner = Partner::with([
            'catalog',
            'user',
            'wilaya',
            'contacts',
            'schedules',
            'services.catalog',
        ])->find($id);

        if (! $partner) {
            return response()->json([
                'message' => 'Center not found.',
            ], 404);
        }

        return response()->json([
            'center' => $partner->formatForPatient(true),
        ]);
    }
}
