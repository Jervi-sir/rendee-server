<?php

namespace App\Http\Controllers\V1\Api\Common;

use App\Http\Controllers\Controller;
use App\Models\ContactPlatform;
use App\Models\PartnerService;
use App\Models\ProfessionalSpeciality;
use App\Models\ServiceCatalog;
use App\Models\Status;
use App\Models\UserRole;
use App\Models\Wilaya;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    /**
     * Allowed catalog includes mapped to their model class.
     *
     * @var array<string, class-string<Model>>
     */
    private const CATALOG_MAP = [
        'professional_specialities' => ProfessionalSpeciality::class,
        'contact_platforms' => ContactPlatform::class,
        'service_catalogs' => ServiceCatalog::class,
        'wilayas' => Wilaya::class,
        'statuses' => Status::class,
        'user_roles' => UserRole::class,
        'partner_services' => PartnerService::class,
        'registery_types' => null,
    ];

    /**
     * Get catalog data.
     *
     * Usage: GET /catalogs?includes=professional_specialities,wilayas,user_roles,registery_types&source=doctor
     */
    public function index(Request $request): JsonResponse
    {
        $rawIncludes = $request->query('includes', '');
        if ($rawIncludes === '' || $rawIncludes === 'all') {
            $includes = array_keys(self::CATALOG_MAP);
        } else {
            $includes = $this->parseIncludes($rawIncludes);
        }

        $data = [];

        foreach ($includes as $include) {
            if ($include === 'registery_types') {
                $userRoles = UserRole::where('code', UserRole::PATIENT)
                    ->get()
                    ->map(function ($role) {
                        $roleArr = $role->toArray();
                        $roleArr['source'] = 'user_role';

                        return $roleArr;
                    });

                $partnerTypes = \App\Models\PartnerType::all()
                    ->map(function ($type) {
                        $typeArr = $type->toArray();
                        $typeArr['source'] = 'partner_type';

                        return $typeArr;
                    });

                $data['registery_types'] = $userRoles->merge($partnerTypes)->values();

                continue;
            }

            if (! isset(self::CATALOG_MAP[$include]) || self::CATALOG_MAP[$include] === null) {
                continue;
            }

            $modelClass = self::CATALOG_MAP[$include];
            $query = $modelClass::query();

            // Apply source filter only to service_catalogs
            if ($include === 'service_catalogs' && $request->has('source')) {
                $query->where('source', $request->query('source'));
            }

            // Filter admin role out if user_roles
            if ($include === 'user_roles') {
                $query->where('code', '!=', UserRole::ADMIN);
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
