<?php

namespace App\Http\Controllers\V1\Api\Common;

use App\Http\Controllers\Controller;
use App\Models\ContactPlatform;
use App\Models\ProfessionalService;
use App\Models\ProfessionalSpeciality;
use App\Models\ServiceCatalog;
use App\Models\Status;
use App\Models\UserRole;
use App\Models\Wilaya;
use Illuminate\Database\Eloquent\Model;
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
        'professional_services' => ProfessionalService::class,
    ];

    /**
     * Get catalog data.
     *
     * Usage: GET /catalogs?includes=professional_specialities,wilayas,service_catalogs&source=doctor
     *
     * The `source` filter only applies to `service_catalogs`.
     */
    public function index(Request $request): JsonResponse
    {
        $includes = $this->parseIncludes($request->query('includes', ''));

        if ($includes === []) {
            return response()->json([
                'message' => 'No includes specified. Available: '.implode(', ', array_keys(self::CATALOG_MAP)),
            ], 422);
        }

        $data = [];

        foreach ($includes as $include) {
            if (! isset(self::CATALOG_MAP[$include])) {
                continue;
            }

            $modelClass = self::CATALOG_MAP[$include];
            $query = $modelClass::query();

            // Apply source filter only to service_catalogs
            if ($include === 'service_catalogs' && $request->has('source')) {
                $query->where('source', $request->query('source'));
            }

            // Exclude admin role from user_roles
            if ($include === 'user_roles') {
                $query->where('code', '!=', 'admin');
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
            fn (string $value): bool => $value !== '' && isset(self::CATALOG_MAP[$value]),
        ));
    }
}
