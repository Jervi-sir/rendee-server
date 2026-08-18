<?php

namespace App\Http\Controllers\V1\Api\Common;

use App\Http\Controllers\Controller;
use App\Models\CenterCatalog;
use App\Models\ContactPlatform;
use App\Models\PartnerService;
use App\Models\PartnerType;
use App\Models\Profession;
use App\Models\ServiceCatalog;
use App\Models\Speciality;
use App\Models\Status;
use App\Models\UserRole;
use App\Models\Wilaya;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    /**
     * Allowed catalog includes mapped to their model class.
     *
     * @var array<string, class-string<\Illuminate\Database\Eloquent\Model>|null>
     */
    private const CATALOG_MAP = [
        'specialities' => Speciality::class,
        'professional_specialities' => Speciality::class, // Backwards-compatible alias
        'professions' => Profession::class,
        'partner_types' => PartnerType::class,
        'center_catalogs' => CenterCatalog::class,
        'service_catalogs' => ServiceCatalog::class,
        'wilayas' => Wilaya::class,
        'contact_platforms' => ContactPlatform::class,
        'statuses' => Status::class,
        'user_roles' => UserRole::class,
        'partner_services' => PartnerService::class,
        'professional_services' => PartnerService::class, // Backwards-compatible alias
        'registery_types' => null,                         // Virtual merged resource
    ];

    /**
     * Get reference catalog data with dynamic includes and filters.
     *
     * Usage examples:
     * - GET /catalogs?includes=specialities,wilayas&profession=doctor
     * - GET /catalogs?includes=service_catalogs&source=doctor
     * - GET /catalogs?includes=all
     */
    public function index(Request $request): JsonResponse
    {
        $rawIncludes = $request->query('includes');

        if ($rawIncludes === null || trim($rawIncludes) === '') {
            return response()->json([
                'message' => 'The includes query parameter is required. Example: ?includes=specialities,wilayas',
            ], 422);
        }

        $rawIncludes = trim($rawIncludes);
        if ($rawIncludes === 'all') {
            $includes = array_keys(self::CATALOG_MAP);
        } else {
            $includes = $this->parseIncludes($rawIncludes);
        }

        $data = [];

        foreach ($includes as $include) {
            // 1. Handle Virtual registery_types (Patient user role + Partner registration types)
            if ($include === 'registery_types') {
                $userRoles = UserRole::where('code', UserRole::PATIENT)
                    ->get()
                    ->map(function (UserRole $role) {
                        $arr = $role->toArray();
                        $arr['source'] = 'user_role';

                        return $arr;
                    });

                $partnerTypes = PartnerType::all()
                    ->map(function (PartnerType $type) {
                        $arr = $type->toArray();
                        $arr['source'] = 'partner_type';

                        return $arr;
                    });

                $data['registery_types'] = $userRoles->merge($partnerTypes)->values();

                continue;
            }

            if (! isset(self::CATALOG_MAP[$include]) || self::CATALOG_MAP[$include] === null) {
                continue;
            }

            $modelClass = self::CATALOG_MAP[$include];
            /** @var Builder $query */
            $query = $modelClass::query();

            // 2. Apply Domain Filters
            switch ($include) {
                case 'specialities':
                case 'professional_specialities':
                    if ($request->filled('profession') || $request->filled('profession_code')) {
                        $profession = $request->query('profession') ?? $request->query('profession_code');
                        $query->where('profession_code', $profession);
                    }
                    $query->orderBy('code', 'asc');
                    break;

                case 'professions':
                    if ($request->filled('partner_type') || $request->filled('partner_type_code')) {
                        $partnerType = $request->query('partner_type') ?? $request->query('partner_type_code');
                        $query->where('partner_type_code', $partnerType);
                    }
                    if ($request->boolean('with_specialities')) {
                        $query->with('specialities');
                    }
                    if ($request->boolean('with_partner_type')) {
                        $query->with('partnerType');
                    }
                    $query->orderBy('code', 'asc');
                    break;

                case 'service_catalogs':
                    if ($request->filled('source')) {
                        $query->where('source', $request->query('source'));
                    }
                    $query->orderBy('code', 'asc');
                    break;

                case 'wilayas':
                    $query->orderBy('code', 'asc');
                    break;

                case 'user_roles':
                    // Do not expose admin role publicly
                    $query->where('code', '!=', UserRole::ADMIN)->orderBy('code', 'asc');
                    break;

                case 'partner_services':
                case 'professional_services':
                    $query->where('is_active', true);
                    if ($request->filled('partner_id')) {
                        $query->where('partner_id', $request->query('partner_id'));
                    }
                    break;

                default:
                    $query->orderBy('code', 'asc');
                    break;
            }

            $data[$include] = $query->get();
        }

        return response()->json($data);
    }

    /**
     * Parse the comma-separated includes string into an array.
     *
     * @return string[]
     */
    private function parseIncludes(string $raw): array
    {
        if ($raw === '' || $raw === '0') {
            return [];
        }

        return array_values(array_filter(
            array_map('trim', explode(',', $raw)),
            fn(string $value): bool => $value !== '' && array_key_exists($value, self::CATALOG_MAP),
        ));
    }
}
