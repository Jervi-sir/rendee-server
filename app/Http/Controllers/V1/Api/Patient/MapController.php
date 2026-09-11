<?php

namespace App\Http\Controllers\V1\Api\Patient;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Models\PartnerType;
use App\Models\Wilaya;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MapController extends Controller
{
    /**
     * GET /api/v1/patient/map
     *
     * Query Parameters:
     * - `partner_type` (string, optional): Filter by partner type ("doctor", "center", "dentist", "pharmacist")
     * - `profession` (string, optional): Filter by profession code ("doctor", "dentist", "psychologist", etc.)
     * - `speciality` (string, optional): Filter by speciality code ("cardiology", "pediatrics", etc.)
     * - `wilaya_code` (string, optional): Filter by Wilaya code (e.g. "16", "31")
     * - `query` (string, optional): Search keyword
     *
     * Response JSON:
     * {
     *   "markers": [
     *     {
     *       "id": 4,
     *       "lat": 36.7538,
     *       "lng": 3.0588,
     *       "title": "د. كريم عمراني",
     *       "address": "12 Rue Didouche Mourad, Alger",
     *       "partner_type": {
     *         "code": "doctor",
     *         "label": "طبيب / أخصائي"
     *       },
     *       "pin_color": "#0284C7"
     *     }
     *   ],
     *   "filters": [
     *     { "key": "all", "label": "الكل", "count": 24 },
     *     { "key": "doctor", "label": "أطباء", "count": 12 }
     *   ],
     *   "wilayas": [ ... ]
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $entityType = $request->query('partner_type') ?? $request->query('user_type') ?? $request->query('entity_type');
        $professionCode = $request->query('profession_code') ?? $request->query('profession');
        $specialityCode = $request->query('speciality_code') ?? $request->query('speciality');
        $wilayaCode = $request->query('wilaya_code') ?? $request->query('wilaya');
        $queryStr = $request->query('query');

        $query = Partner::with(['user', 'partnerType', 'profession', 'speciality', 'catalog', 'wilaya'])
            ->where('is_active', true)
            ->where('is_available', true);

        if ($queryStr) {
            $query->where(function ($q) use ($queryStr) {
                $q->where('name', 'like', "%{$queryStr}%")
                    ->orWhere('city', 'like', "%{$queryStr}%")
                    ->orWhere('address', 'like', "%{$queryStr}%")
                    ->orWhereHas('user', function ($uq) use ($queryStr) {
                        $uq->where('full_name', 'like', "%{$queryStr}%")
                            ->orWhere('name', 'like', "%{$queryStr}%");
                    })
                    ->orWhereHas('speciality', function ($sq) use ($queryStr) {
                        $sq->where('ar', 'like', "%{$queryStr}%")
                            ->orWhere('fr', 'like', "%{$queryStr}%")
                            ->orWhere('en', 'like', "%{$queryStr}%");
                    });
            });
        }

        if ($entityType && $entityType !== 'all') {
            $query->where('partner_type_code', $entityType);
        }

        if ($professionCode && $professionCode !== 'all') {
            $query->where(function ($pq) use ($professionCode) {
                $pq->where('profession_code', $professionCode)
                    ->orWhere('partner_type_code', $professionCode);
            });
        }

        if ($specialityCode && $specialityCode !== 'all') {
            $query->where(function ($sq) use ($specialityCode) {
                $sq->where('speciality_code', $specialityCode)
                    ->orWhere('center_catalog_code', $specialityCode);
            });
        }

        if ($wilayaCode && $wilayaCode !== 'all') {
            $query->where('wilaya_code', $wilayaCode);
        }

        $partners = $query->get();

        $markers = [];
        foreach ($partners as $partner) {
            $code = $partner->partner_type_code ?? 'doctor';
            $label = $partner->partnerType?->ar
                ?? $partner->partnerType?->fr
                ?? match ($code) {
                    'center' => 'مركز طبي',
                    'pharmacy', 'pharmacist' => 'صيدلية',
                    default => 'طبيب / أخصائي',
                };

            $title = $partner->name ?? $partner->user?->full_name ?? $partner->user?->name ?? 'شريك';
            if (in_array($code, ['doctor', 'professional']) && ! str_starts_with($title, 'د.')) {
                $title = $title;
            }

            $pinColor = match ($code) {
                'pharmacy', 'pharmacist' => '#059669',
                'center' => '#1E3A8A',
                'dentist' => '#10B981',
                default => '#0284C7',
            };

            $markers[] = [
                'id' => (int) $partner->id,
                'lat' => $partner->latitude ? (float) $partner->latitude : 36.7538,
                'lng' => $partner->longitude ? (float) $partner->longitude : 3.0588,
                'title' => $title,
                'address' => $partner->address ?? $partner->city ?? 'الجزائر',
                'partner_type' => [
                    'code' => $code,
                    'label' => $label,
                ],
                'pin_color' => $pinColor,
            ];
        }

        // Dynamic filters matching FeedController
        $partnerTypes = PartnerType::all();

        $filters = [
            [
                'key' => 'all',
                'label' => 'الكل',
                'count' => count($markers),
            ],
        ];

        foreach ($partnerTypes as $partnerType) {
            $code = $partnerType->code;
            $label = $partnerType->ar ?? $partnerType->fr ?? $partnerType->en ?? $code;
            $count = count(array_filter($markers, function ($m) use ($code) {
                $pt = $m['partner_type']['code'] ?? '';
                if ($code === 'doctor' || $code === 'professional') {
                    return in_array($pt, ['doctor', 'professional']);
                }

                return $pt === $code;
            }));

            $filters[] = [
                'key' => $code,
                'label' => $label,
                'count' => $count,
            ];
        }

        return response()->json([
            'markers' => $markers,
            'filters' => $filters,
            'wilayas' => Wilaya::getActiveWilayas(),
        ]);
    }
}
