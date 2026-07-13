<?php

namespace App\Http\Controllers\V1\Api\Professional;

use App\Http\Controllers\Controller;
use App\Models\Professional;
use App\Models\ProfessionalService;
use App\Models\ServiceCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{
    /**
     * Get list of services offered by the professional.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $professional = null;

        if ($user) {
            $professional = Professional::where('user_id', $user->id)->first();
        }

        if (!$professional) {
            $professional = Professional::first();
        }

        if (!$professional) {
            return response()->json(['services' => []]);
        }

        $services = ProfessionalService::with('serviceCatalog')
            ->where('professional_id', $professional->id)
            ->get();

        return response()->json([
            'services' => $services,
        ]);
    }

    /**
     * Get the list of global service catalogs that can be added.
     */
    public function catalog(Request $request): JsonResponse
    {
        $catalogs = ServiceCatalog::where(function ($q) {
            $q->where('source', 'doctor')
              ->orWhere('source', 'professional')
              ->orWhereNull('source');
        })->get();

        return response()->json([
            'catalog' => $catalogs,
        ]);
    }

    /**
     * Add a service to the professional's profile.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        $professional = null;

        if ($user) {
            $professional = Professional::where('user_id', $user->id)->first();
        }

        if (!$professional) {
            $professional = Professional::first();
        }

        if (!$professional) {
            return response()->json(['error' => 'Professional profile not found'], 404);
        }

        $validated = $request->validate([
            'service_catalog_code' => ['required', 'string', Rule::exists('service_catalogs', 'code')],
            'price' => ['required', 'numeric', 'min:0'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:480'],
        ]);

        // Check if service already exists
        $exists = ProfessionalService::where('professional_id', $professional->id)
            ->where('service_catalog_code', $validated['service_catalog_code'])
            ->exists();

        if ($exists) {
            return response()->json([
                'error' => 'You have already added this service to your profile.',
            ], 422);
        }

        $service = ProfessionalService::create([
            'professional_id' => $professional->id,
            'service_catalog_code' => $validated['service_catalog_code'],
            'price' => $validated['price'],
            'duration_minutes' => $validated['duration_minutes'],
        ]);

        return response()->json([
            'success' => true,
            'service' => $service->load('serviceCatalog'),
        ], 201);
    }

    /**
     * Update an existing service.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $professional = null;

        if ($user) {
            $professional = Professional::where('user_id', $user->id)->first();
        }

        if (!$professional) {
            $professional = Professional::first();
        }

        $service = ProfessionalService::where('id', $id);
        if ($professional) {
            $service->where('professional_id', $professional->id);
        }
        $service = $service->first();

        if (!$service) {
            return response()->json(['error' => 'Service not found'], 404);
        }

        $validated = $request->validate([
            'price' => ['required', 'numeric', 'min:0'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:480'],
        ]);

        $service->update([
            'price' => $validated['price'],
            'duration_minutes' => $validated['duration_minutes'],
        ]);

        return response()->json([
            'success' => true,
            'service' => $service->load('serviceCatalog'),
        ]);
    }

    /**
     * Remove a service.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $professional = null;

        if ($user) {
            $professional = Professional::where('user_id', $user->id)->first();
        }

        if (!$professional) {
            $professional = Professional::first();
        }

        $service = ProfessionalService::where('id', $id);
        if ($professional) {
            $service->where('professional_id', $professional->id);
        }
        $service = $service->first();

        if (!$service) {
            return response()->json(['error' => 'Service not found'], 404);
        }

        $service->delete();

        return response()->json([
            'success' => true,
            'message' => 'Service removed successfully.',
        ]);
    }
}
