<?php

namespace App\Http\Controllers\V1\Api\Patient;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MapController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $entityType = $request->query('partner_type') ?? $request->query('user_type') ?? $request->query('entity_type');
        $wilayaCode = $request->query('wilaya_code') ?? $request->query('wilaya');
        $queryStr = $request->query('query');

        $query = Partner::with(['user', 'partnerType', 'specialty', 'catalog', 'wilaya'])
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
                    });
            });
        }

        if ($entityType && $entityType !== 'all') {
            $query->where('partner_type_code', $entityType);
        }

        if ($wilayaCode && $wilayaCode !== 'all') {
            $query->where('wilaya_code', $wilayaCode);
        }

        $partners = $query->get();

        $markers = [];
        foreach ($partners as $partner) {
            $code = $partner->partner_type_code ?? 'doctor';
            $label = $partner->partnerType?->ar
                ?? $partner->partnerType?->en
                ?? match ($code) {
                    'center' => 'مركز طبي',
                    'pharmacy', 'pharmacist' => 'صيدلية',
                    default => 'طبيب / أخصائي',
                };

            $title = $partner->name ?? $partner->user?->full_name ?? $partner->user?->name ?? 'شريك';
            if (in_array($code, ['doctor', 'professional']) && ! str_starts_with($title, 'د.')) {
                $title = 'د. ' . $title;
            }

            $pinColor = match ($code) {
                'pharmacy', 'pharmacist' => '#059669',
                'center' => '#1E3A8A',
                default => '#0284C7',
            };

            $markers[] = [
                'id' => (int) $partner->id,
                'lat' => $partner->latitude ? (float) $partner->latitude : 35.6969,
                'lng' => $partner->longitude ? (float) $partner->longitude : -0.6331,
                'title' => $title,
                'address' => $partner->address ?? $partner->city ?? 'وهران',
                'partner_type' => [
                    'code' => $code,
                    'label' => $label,
                ],
                'pin_color' => $pinColor,
            ];
        }

        // Dynamic filters matching FeedController structure
        $typeLabels = [
            'doctor' => 'أطباء',
            'dentist' => 'أطباء الأسنان',
            'pharmacist' => 'صيدليات',
            'center' => 'مراكز طبية',
            'psy' => 'أطباء نفسيون',
            'professional' => 'مهنيون',
        ];

        $filters = [
            [
                'key' => 'all',
                'label' => 'الكل',
                'count' => count($markers),
            ],
        ];

        foreach ($typeLabels as $code => $defaultLabel) {
            $partnerTypeObj = \App\Models\PartnerType::find($code);
            $label = $partnerTypeObj?->ar ?? $partnerTypeObj?->en ?? $defaultLabel;
            $count = count(array_filter($partners->toArray(), function ($p) use ($code) {
                $pt = $p['partner_type_code'] ?? $p['partner_type'] ?? '';
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
            'wilayas' => \App\Models\Wilaya::getActiveWilayas(),
        ]);
    }
}
