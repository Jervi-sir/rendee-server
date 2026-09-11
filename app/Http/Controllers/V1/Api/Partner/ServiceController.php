<?php

namespace App\Http\Controllers\V1\Api\Partner;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Models\PartnerService;
use App\Models\ServiceCatalog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{
    /**
     * Get list of services provided by the authenticated partner.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            $user = User::first();
        }

        if (! $user) {
            return response()->json(['success' => true, 'services' => []]);
        }

        $partner = Partner::where('user_id', $user->id)->first();
        if (! $partner) {
            $partner = Partner::first();
        }

        if (! $partner) {
            return response()->json(['success' => true, 'services' => []]);
        }

        $services = PartnerService::with('catalog')
            ->where('partner_id', $partner->id)
            ->get()
            ->map(fn ($service) => [
                'id' => $service->id,
                'service_catalog_code' => $service->service_catalog_code,
                'name' => $service->catalog?->ar ?? $service->catalog?->en ?? $service->name ?? 'خدمة بدون عنوان',
                'name_ar' => $service->catalog?->ar ?? $service->name,
                'name_en' => $service->catalog?->en ?? $service->name,
                'name_fr' => $service->catalog?->fr,
                'description' => $service->description,
                'price' => (float) $service->price,
                'duration_minutes' => (int) ($service->duration_minutes ?? 30),
                'is_active' => (bool) ($service->is_active ?? true),
                'created_at' => $service->created_at?->toIso8601String(),
            ]);

        return response()->json(['success' => true, 'services' => $services]);
    }

    /**
     * Get available service catalog items for selection.
     */
    public function catalog(Request $request): JsonResponse
    {
        $catalogs = ServiceCatalog::all()->map(fn ($cat) => [
            'code' => $cat->code,
            'name' => $cat->ar ?? $cat->en ?? $cat->code,
            'name_ar' => $cat->ar,
            'name_en' => $cat->en,
            'name_fr' => $cat->fr,
            'source' => $cat->source,
        ]);

        return response()->json([
            'success' => true,
            'catalog' => $catalogs,
        ]);
    }

    /**
     * Add a service with name and price for the partner profile.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            $user = User::first();
        }

        if (! $user) {
            return response()->json(['error' => 'Partner account not found'], 404);
        }

        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'name_ar' => ['nullable', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'name_fr' => ['nullable', 'string', 'max:255'],
            'service_catalog_code' => ['nullable', 'string', Rule::exists('service_catalogs', 'code')],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'duration_minutes' => ['nullable', 'integer', 'min:5', 'max:480'],
        ]);

        $serviceCatalogCode = $validated['service_catalog_code'] ?? null;
        $nameAr = $validated['name_ar'] ?? $validated['name'] ?? null;
        $nameEn = $validated['name_en'] ?? $validated['name'] ?? null;
        $nameFr = $validated['name_fr'] ?? null;

        if (! $serviceCatalogCode) {
            if (! $nameAr && ! $nameEn) {
                return response()->json(['error' => 'Either name or service_catalog_code is required.'], 422);
            }

            $baseCode = Str::slug($nameEn ?? $nameAr, '_');
            if (empty($baseCode)) {
                $baseCode = 'custom_service_'.time();
            }

            $serviceCatalogCode = 'partner_'.$baseCode;

            ServiceCatalog::firstOrCreate(
                ['code' => $serviceCatalogCode],
                [
                    'source' => 'partner',
                    'ar' => $nameAr,
                    'en' => $nameEn ?? $nameAr,
                    'fr' => $nameFr,
                ]
            );
        }

        $professionCode = match ($user->user_role_code) {
            'pharmacist' => 'pharmacist',
            'center' => 'center',
            default => 'doctor',
        };

        $partner = Partner::firstOrCreate(
            ['user_id' => $user->id],
            ['profession_code' => $professionCode, 'name' => $user->full_name ?? $user->name]
        );

        $service = PartnerService::create([
            'partner_id' => $partner->id,
            'service_catalog_code' => $serviceCatalogCode,
            'name' => $nameAr ?? $nameEn,
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'duration_minutes' => $validated['duration_minutes'] ?? 30,
            'is_active' => true,
        ]);

        $service->load('catalog');

        return response()->json([
            'success' => true,
            'message' => 'Service added successfully.',
            'service' => [
                'id' => $service->id,
                'service_catalog_code' => $service->service_catalog_code,
                'name' => $service->catalog?->ar ?? $service->catalog?->en ?? $service->name ?? 'خدمة',
                'name_ar' => $service->catalog?->ar ?? $service->name,
                'name_en' => $service->catalog?->en ?? $service->name,
                'name_fr' => $service->catalog?->fr,
                'price' => (float) $service->price,
                'duration_minutes' => (int) $service->duration_minutes,
                'created_at' => $service->created_at?->toIso8601String(),
            ],
        ], 201);
    }

    /**
     * Update price, duration, or name of a partner service.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            $user = User::first();
        }

        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'name_ar' => ['nullable', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'name_fr' => ['nullable', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'duration_minutes' => ['nullable', 'integer', 'min:5', 'max:480'],
        ]);

        $service = PartnerService::with('catalog')->find($id);

        if (! $service) {
            return response()->json(['error' => 'Service not found'], 404);
        }

        $service->update([
            'name' => $validated['name_ar'] ?? $validated['name'] ?? $service->name,
            'price' => $validated['price'],
            'duration_minutes' => $validated['duration_minutes'] ?? $service->duration_minutes ?? 30,
        ]);

        if ($service->catalog) {
            $catalogUpdates = [];
            if (! empty($validated['name_ar'])) {
                $catalogUpdates['ar'] = $validated['name_ar'];
            } elseif (! empty($validated['name'])) {
                $catalogUpdates['ar'] = $validated['name'];
            }

            if (! empty($validated['name_en'])) {
                $catalogUpdates['en'] = $validated['name_en'];
            } elseif (! empty($validated['name'])) {
                $catalogUpdates['en'] = $validated['name'];
            }

            if (! empty($validated['name_fr'])) {
                $catalogUpdates['fr'] = $validated['name_fr'];
            }

            if (! empty($catalogUpdates)) {
                $service->catalog->update($catalogUpdates);
            }
        }

        $service->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Service updated successfully.',
            'service' => [
                'id' => $service->id,
                'service_catalog_code' => $service->service_catalog_code,
                'name' => $service->catalog?->ar ?? $service->catalog?->en ?? $service->name ?? 'خدمة',
                'name_ar' => $service->catalog?->ar ?? $service->name,
                'name_en' => $service->catalog?->en ?? $service->name,
                'name_fr' => $service->catalog?->fr,
                'price' => (float) $service->price,
                'duration_minutes' => (int) $service->duration_minutes,
                'created_at' => $service->created_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Delete a partner service.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $service = PartnerService::find($id);

        if (! $service) {
            return response()->json(['error' => 'Service not found'], 404);
        }

        $service->delete();

        return response()->json([
            'success' => true,
            'message' => 'Service deleted successfully.',
        ]);
    }
}
