<?php

namespace App\Http\Controllers\Admin\Partners;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Models\Profession;
use App\Models\Speciality;
use App\Models\Wilaya;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PartnerController extends Controller
{
    /**
     * Display a listing of partners with filters and pagination.
     */
    public function index(Request $request): Response
    {
        $search = $request->query('search');
        $partnerTypeCode = $request->query('partner_type') ?? $request->query('profession');
        $status = $request->query('status'); // 'all', 'active', 'inactive'
        $wilayaCode = $request->query('wilaya');
        $perPage = (int) $request->query('per_page', 12);

        $query = Partner::query()
            ->with([
                'user:id,name,full_name,email,phone_number,image_url',
                'profession:code,en,fr,ar,hex',
                'speciality:code,en,fr,ar',
                'wilaya:code,number,en,fr,ar',
            ])
            ->withCount(['services', 'schedules']);

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone_public', 'like', "%{$search}%")
                    ->orWhere('license_number', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('full_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone_number', 'like', "%{$search}%");
                    });
            });
        }

        if (! empty($partnerTypeCode) && $partnerTypeCode !== 'all') {
            $query->where('profession_code', $partnerTypeCode);
        }

        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        if (! empty($wilayaCode) && $wilayaCode !== 'all') {
            $query->where('wilaya_code', $wilayaCode);
        }

        $partners = $query->latest('id')->paginate($perPage)->withQueryString();

        $professions = Profession::query()->orderBy('en')->get(['code', 'en', 'fr', 'ar', 'hex']);
        $partnerTypes = $professions;
        $specialities = Speciality::query()->orderBy('en')->get(['code', 'en', 'fr', 'ar', 'profession_code']);
        $wilayas = Wilaya::query()->orderBy('number')->get(['code', 'number', 'en', 'fr', 'ar']);
        $catalogs = [];

        return Inertia::render('admin/partners/list', [
            'partners' => $partners,
            'partnerTypes' => $partnerTypes,
            'professions' => $professions,
            'specialities' => $specialities,
            'wilayas' => $wilayas,
            'catalogs' => $catalogs,
            'filters' => [
                'search' => $search ?? '',
                'partner_type' => $partnerTypeCode ?? 'all',
                'status' => $status ?? 'all',
                'wilaya' => $wilayaCode ?? 'all',
                'per_page' => $perPage,
            ],
        ]);
    }

    /**
     * Display a specific partner profile with full relations.
     */
    public function show(Partner $partner): Response
    {
        $partner->load([
            'user',
            'profession',
            'speciality',
            'wilaya',
            'schedules',
            'services',
            'contacts.platform',
        ]);

        $professions = Profession::query()->orderBy('en')->get(['code', 'en', 'fr', 'ar', 'hex']);
        $partnerTypes = $professions;
        $specialities = Speciality::query()->orderBy('en')->get(['code', 'en', 'fr', 'ar', 'profession_code']);
        $wilayas = Wilaya::query()->orderBy('number')->get(['code', 'number', 'en', 'fr', 'ar']);
        $catalogs = [];

        return Inertia::render('admin/partners/show', [
            'partner' => $partner,
            'partnerTypes' => $partnerTypes,
            'professions' => $professions,
            'specialities' => $specialities,
            'wilayas' => $wilayas,
            'catalogs' => $catalogs,
        ]);
    }

    /**
     * Update or approve a partner.
     */
    public function update(Request $request, Partner $partner): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'profession_code' => ['nullable', 'string', 'exists:professions,code'],
            'speciality_code' => ['nullable', 'string', 'exists:specialities,code'],
            'custom_speciality' => ['nullable', 'string', 'max:255'],
            'wilaya_code' => ['nullable', 'string', 'exists:wilayas,code'],
            'license_number' => ['nullable', 'string', 'max:100'],
            'years_experience' => ['nullable', 'string', 'max:50'],
            'phone_public' => ['nullable', 'string', 'max:50'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'lat' => ['nullable', 'numeric'],
            'lng' => ['nullable', 'numeric'],
            'is_available' => ['nullable', 'boolean'],
            'emergency_24_7' => ['nullable', 'boolean'],
            'is_on_duty' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $partner->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Partner updated successfully.',
                'partner' => $partner->fresh([
                    'user:id,name,full_name,email,phone_number,image_url',
                    'profession',
                    'speciality',
                    'wilaya',
                ]),
            ]);
        }

        return back()->with('success', 'Partner updated successfully.');
    }

    /**
     * Toggle active / approval status for a partner.
     */
    public function toggleStatus(Request $request, Partner $partner): RedirectResponse|JsonResponse
    {
        $partner->update([
            'is_active' => ! $partner->is_active,
        ]);

        $statusText = $partner->is_active ? 'approved/activated' : 'deactivated';

        if ($request->wantsJson()) {
            return response()->json([
                'message' => "Partner has been {$statusText}.",
                'is_active' => $partner->is_active,
            ]);
        }

        return back()->with('success', "Partner has been {$statusText}.");
    }

    /**
     * Delete / soft-delete partner.
     */
    public function destroy(Request $request, Partner $partner): RedirectResponse|JsonResponse
    {
        $partner->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Partner deleted successfully.',
            ]);
        }

        return redirect()->route('admin.partners.index')->with('success', 'Partner deleted successfully.');
    }
}
