<?php

namespace App\Http\Controllers\Admin\Catalogs;

use App\Http\Controllers\Controller;
use App\Models\PartnerType;
use App\Models\Profession;
use App\Models\Speciality;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ProfessionController extends Controller
{
    /**
     * Display a listing of professions with partner type filtering and pagination.
     */
    public function index(Request $request): Response
    {
        $partnerTypeCode = $request->query('partner_type');
        $search = $request->query('search');
        $perPage = (int) $request->query('per_page', 15);

        $query = Profession::query()
            ->with(['partnerType'])
            ->withCount('specialities');

        if (! empty($partnerTypeCode) && $partnerTypeCode !== 'all') {
            $query->where('partner_type_code', $partnerTypeCode);
        }

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('en', 'like', "%{$search}%")
                    ->orWhere('fr', 'like', "%{$search}%")
                    ->orWhere('ar', 'like', "%{$search}%");
            });
        }

        $professions = $query->latest()->paginate($perPage)->withQueryString();

        $partnerTypes = PartnerType::query()
            ->orderBy('en')
            ->get(['code', 'en', 'fr', 'ar']);

        return Inertia::render('admin/catalogs/professions', [
            'professions' => $professions,
            'partnerTypes' => $partnerTypes,
            'filters' => [
                'partner_type' => $partnerTypeCode ?? 'all',
                'search' => $search ?? '',
                'per_page' => $perPage,
            ],
        ]);
    }

    /**
     * Store or update a profession.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('professions', 'code')],
            'partner_type_code' => ['nullable', 'string', 'exists:partner_types,code'],
            'en' => ['required', 'string', 'max:255'],
            'fr' => ['nullable', 'string', 'max:255'],
            'ar' => ['nullable', 'string', 'max:255'],
            'hex' => ['required', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
        ]);

        $profession = Profession::create($validated);
        $profession->load(['partnerType'])->loadCount('specialities');

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Profession created successfully.',
                'profession' => $profession,
            ], 201);
        }

        return back()->with('success', 'Profession created successfully.');
    }

    /**
     * Update an existing profession.
     */
    public function update(Request $request, Profession $profession): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'partner_type_code' => ['nullable', 'string', 'exists:partner_types,code'],
            'en' => ['required', 'string', 'max:255'],
            'fr' => ['nullable', 'string', 'max:255'],
            'ar' => ['nullable', 'string', 'max:255'],
            'hex' => ['required', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
        ]);

        $profession->update($validated);
        $profession->load(['partnerType'])->loadCount('specialities');

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Profession updated successfully.',
                'profession' => $profession,
            ]);
        }

        return back()->with('success', 'Profession updated successfully.');
    }

    /**
     * Delete a profession.
     */
    public function destroy(Request $request, Profession $profession): RedirectResponse|JsonResponse
    {
        $profession->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Profession deleted successfully.',
            ]);
        }

        return back()->with('success', 'Profession deleted successfully.');
    }

    /**
     * Return specialities under a given profession.
     */
    public function specialities(Profession $profession): JsonResponse
    {
        $specialities = $profession->specialities()
            ->latest()
            ->get();

        return response()->json([
            'profession' => $profession,
            'specialities' => $specialities,
        ]);
    }

    /**
     * Upsert a speciality under a given profession.
     */
    public function storeSpeciality(Request $request, Profession $profession): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'alpha_dash'],
            'en' => ['required', 'string', 'max:255'],
            'fr' => ['nullable', 'string', 'max:255'],
            'ar' => ['nullable', 'string', 'max:255'],
        ]);

        $speciality = Speciality::updateOrCreate(
            ['code' => $validated['code']],
            [
                'profession_code' => $profession->code,
                'en' => $validated['en'],
                'fr' => $validated['fr'] ?? null,
                'ar' => $validated['ar'] ?? null,
            ]
        );

        return response()->json([
            'message' => 'Speciality saved successfully.',
            'speciality' => $speciality,
        ], 200);
    }

    /**
     * Delete a speciality.
     */
    public function destroySpeciality(Speciality $speciality): JsonResponse
    {
        $speciality->delete();

        return response()->json([
            'message' => 'Speciality deleted successfully.',
        ]);
    }
}
