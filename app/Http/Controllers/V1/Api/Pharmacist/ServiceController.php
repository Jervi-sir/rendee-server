<?php

namespace App\Http\Controllers\V1\Api\Pharmacist;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * Get list of services offered by the pharmacy.
     */
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'services' => [],
        ]);
    }

    /**
     * Get the list of global service catalogs that can be added by pharmacies.
     */
    public function catalog(Request $request): JsonResponse
    {
        return response()->json([
            'catalog' => [],
        ]);
    }

    /**
     * Add a service to the pharmacy profile (Stub placeholder).
     */
    public function store(Request $request): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Pharmacy services are not supported under the current database schema.',
        ], 400);
    }

    /**
     * Update an existing service (Stub placeholder).
     */
    public function update(Request $request, int $id): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Pharmacy services are not supported under the current database schema.',
        ], 400);
    }

    /**
     * Remove a service (Stub placeholder).
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Pharmacy services are not supported under the current database schema.',
        ], 400);
    }
}
