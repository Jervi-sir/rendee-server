<?php

namespace App\Http\Controllers\V1\Api\Common;

use App\Http\Controllers\Controller;
use App\Models\ContactPlatform;
use App\Models\ProfessionalSpeciality;
use App\Models\ServiceCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    /**
     * Get all professional specialities.
     */
    public function specialities(): JsonResponse
    {
        $specialities = ProfessionalSpeciality::all();

        return response()->json([
            'specialities' => $specialities,
        ]);
    }

    /**
     * Get all contact platforms.
     */
    public function contactPlatforms(): JsonResponse
    {
        $platforms = ContactPlatform::all();

        return response()->json([
            'platforms' => $platforms,
        ]);
    }

    /**
     * Get service catalog.
     * Supports filtering by source (e.g. ?source=doctor or ?source=center)
     */
    public function services(Request $request): JsonResponse
    {
        $query = ServiceCatalog::query();

        if ($request->has('source')) {
            $query->where('source', $request->query('source'));
        }

        $services = $query->get();

        return response()->json([
            'services' => $services,
        ]);
    }
}
