<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api\Center\M3;

use App\Http\Controllers\Controller;
use App\Models\Center;
use App\Models\CenterService;
use App\Models\ServiceCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CenterServiceFormController extends Controller
{
    private function getCenter(Request $request): ?Center
    {
        $user = $request->user();
        $center = null;
        if ($user) {
            $center = Center::where('user_id', $user->id)->first();
        }
        if (!$center) {
            $center = Center::first();
        }
        return $center;
    }

    private function updateProfileCompleteness(Center $center): void
    {
        $hasServices = CenterService::where('center_id', $center->id)->where('is_active', true)->exists();
        $hasWorkingHours = \App\Models\CenterWorkingHour::where('center_id', $center->id)->where('is_available', true)->exists();

        $isComplete = !empty($center->license_number) &&
                      !empty($center->description) &&
                      !empty($center->address) &&
                      !empty($center->city) &&
                      $hasServices &&
                      $hasWorkingHours;

        if ($center->user) {
            $center->user->profile_complete = $isComplete;
            $center->user->save();
        }
    }

    public function index(Request $request): JsonResponse
    {
        $center = $this->getCenter($request);
        if (!$center) {
            return response()->json(['services' => []]);
        }

        $services = CenterService::where('center_id', $center->id)
            ->with('serviceCatalog')
            ->get();

        $servicesData = [];
        foreach ($services as $s) {
            $name = $s->serviceCatalog?->ar ?? $s->serviceCatalog?->en ?? 'خدمة طبية';
            $servicesData[] = [
                'id' => $s->id,
                'service_id' => $s->serviceCatalog?->id ?? 0,
                'name' => $name,
                'description' => $s->description,
                'price' => (string)$s->price,
                'price_label' => number_format((float)$s->price, 0) . ' ر.س',
                'duration_minutes' => $s->duration_minutes,
                'duration_label' => $s->duration_minutes ? "{$s->duration_minutes} دقيقة" : 'غير محدد',
                'is_active' => (bool)$s->is_active,
                'status_label' => $s->is_active ? 'نشط' : 'غير نشط',
            ];
        }

        return response()->json(['services' => $servicesData]);
    }

    public function catalog(Request $request): JsonResponse
    {
        $catalogs = ServiceCatalog::all();
        $catalogData = [];
        foreach ($catalogs as $c) {
            $catalogData[] = [
                'id' => $c->id,
                'name' => $c->ar ?? $c->en ?? $c->code,
            ];
        }

        return response()->json([
            'services_catalog' => $catalogData,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $center = $this->getCenter($request);
        if (!$center) {
            return response()->json(['error' => 'Center profile not found'], 404);
        }

        $validated = $request->validate([
            'service_id' => 'required|integer',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric',
            'duration_minutes' => 'nullable|integer',
            'is_active' => 'required|boolean',
        ]);

        $serviceCatalog = ServiceCatalog::find($validated['service_id']);
        if (!$serviceCatalog) {
            return response()->json(['error' => 'Selected service catalog not found'], 422);
        }

        // Check if already exists
        $existing = CenterService::where('center_id', $center->id)
            ->where('service_catalog_code', $serviceCatalog->code)
            ->first();

        if ($existing) {
            return response()->json(['message' => 'هذه الخدمة مضافة بالفعل بالمركز'], 422);
        }

        $service = CenterService::create([
            'center_id' => $center->id,
            'service_catalog_code' => $serviceCatalog->code,
            'description' => $validated['description'],
            'price' => $validated['price'],
            'duration_minutes' => $validated['duration_minutes'],
            'is_active' => $validated['is_active'],
        ]);

        $this->updateProfileCompleteness($center);

        return response()->json([
            'success' => true,
            'service' => $service,
        ], 201);
    }

    public function show(Request $request, $id): JsonResponse
    {
        $service = CenterService::with('serviceCatalog')->find($id);

        if (!$service) {
            return response()->json(['error' => 'Service not found'], 404);
        }

        $name = $service->serviceCatalog?->ar ?? $service->serviceCatalog?->en ?? 'خدمة طبية';

        return response()->json([
            'service' => [
                'id' => $service->id,
                'service_id' => $service->serviceCatalog?->id ?? 0,
                'name' => $name,
                'description' => $service->description,
                'price' => (string)$service->price,
                'price_label' => number_format((float)$service->price, 0) . ' ر.س',
                'duration_minutes' => $service->duration_minutes,
                'duration_label' => $service->duration_minutes ? "{$service->duration_minutes} دقيقة" : 'غير محدد',
                'is_active' => (bool)$service->is_active,
                'status_label' => $service->is_active ? 'نشط' : 'غير نشط',
            ]
        ]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $center = $this->getCenter($request);
        if (!$center) {
            return response()->json(['error' => 'Center profile not found'], 404);
        }

        $service = CenterService::where('center_id', $center->id)->find($id);

        if (!$service) {
            return response()->json(['error' => 'Service not found'], 404);
        }

        $validated = $request->validate([
            'service_id' => 'required|integer',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric',
            'duration_minutes' => 'nullable|integer',
            'is_active' => 'required|boolean',
        ]);

        $serviceCatalog = ServiceCatalog::find($validated['service_id']);
        if (!$serviceCatalog) {
            return response()->json(['error' => 'Selected service catalog not found'], 422);
        }

        $service->update([
            'service_catalog_code' => $serviceCatalog->code,
            'description' => $validated['description'],
            'price' => $validated['price'],
            'duration_minutes' => $validated['duration_minutes'],
            'is_active' => $validated['is_active'],
        ]);

        $this->updateProfileCompleteness($center);

        return response()->json([
            'success' => true,
            'service' => $service,
        ]);
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        $center = $this->getCenter($request);
        if (!$center) {
            return response()->json(['error' => 'Center profile not found'], 404);
        }

        $service = CenterService::where('center_id', $center->id)->find($id);

        if (!$service) {
            return response()->json(['error' => 'Service not found'], 404);
        }

        $service->delete();

        $this->updateProfileCompleteness($center);

        return response()->json([
            'success' => true,
        ]);
    }
}
