<?php

namespace App\Http\Controllers\V1\Api\Patient;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Models\Profession;
use App\Models\Wilaya;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MapController extends Controller
{
    /**
     * GET /api/v1/patient/map
     *
     * Query Parameters:
     * - `profession` / `profession_code` / `partner_type` (string, optional): Filter by profession code
     * - `speciality` / `speciality_code` (string, optional): Filter by speciality code
     * - `wilaya_code` (string, optional): Filter by Wilaya code
     * - `query` (string, optional): Search keyword
     */
    public function index(Request $request): JsonResponse
    {
        $professionCode = $request->query('profession_code') ?? $request->query('profession') ?? $request->query('partner_type') ?? $request->query('user_type') ?? $request->query('entity_type');
        $specialityCode = $request->query('speciality_code') ?? $request->query('speciality');
        $wilayaCode = $request->query('wilaya_code') ?? $request->query('wilaya');
        $queryStr = $request->query('query');

        $query = Partner::with(['user', 'profession', 'speciality', 'wilaya', 'commune'])
            ->where('is_active', true)
            ->where('is_available', true);

        if ($queryStr) {
            $query->where(function ($q) use ($queryStr) {
                $q->where('name', 'like', "%{$queryStr}%")
                    ->orWhere('city', 'like', "%{$queryStr}%")
                    ->orWhere('address', 'like', "%{$queryStr}%")
                    ->orWhere('custom_speciality', 'like', "%{$queryStr}%")
                    ->orWhereHas('user', function ($uq) use ($queryStr) {
                        $uq->where('full_name', 'like', "%{$queryStr}%")
                            ->orWhere('name', 'like', "%{$queryStr}%");
                    })
                    ->orWhereHas('speciality', function ($sq) use ($queryStr) {
                        $sq->where('ar', 'like', "%{$queryStr}%")
                            ->orWhere('fr', 'like', "%{$queryStr}%")
                            ->orWhere('en', 'like', "%{$queryStr}%");
                    })
                    ->orWhereHas('profession', function ($pq) use ($queryStr) {
                        $pq->where('ar', 'like', "%{$queryStr}%")
                            ->orWhere('fr', 'like', "%{$queryStr}%")
                            ->orWhere('en', 'like', "%{$queryStr}%");
                    });
            });
        }

        if ($professionCode && $professionCode !== 'all') {
            $query->where('profession_code', $professionCode);
        }

        if ($specialityCode && $specialityCode !== 'all') {
            $query->where(function ($sq) use ($specialityCode) {
                $sq->where('speciality_code', $specialityCode)
                    ->orWhere('custom_speciality', 'like', "%{$specialityCode}%");
            });
        }

        if ($wilayaCode && $wilayaCode !== 'all') {
            $query->where('wilaya_code', $wilayaCode);
        }

        $partners = $query->get();

        $markers = [];
        foreach ($partners as $partner) {
            $code = $partner->profession_code ?? 'doctor';
            $label = $partner->profession?->ar
                ?? $partner->profession?->fr
                ?? $partner->profession?->en
                ?? match ($code) {
                    'center' => 'مركز طبي',
                    'pharmacy', 'pharmacist' => 'صيدلية',
                    default => 'طبيب / أخصائي',
                };

            $title = $partner->name ?? $partner->user?->full_name ?? $partner->user?->name ?? 'شريك';

            $pinColor = $partner->profession?->hex ?? match ($code) {
                'pharmacy', 'pharmacist' => '#059669',
                'center' => '#1E3A8A',
                'dentist' => '#10B981',
                default => '#0284C7',
            };

            $communeLabel = $partner->commune?->ar ?? $partner->commune?->fr ?? $partner->commune?->en ?? $partner->city;

            $specialityName = $partner->speciality?->ar
                ?? $partner->speciality?->fr
                ?? $partner->custom_speciality
                ?? $partner->display_speciality
                ?? $partner->speciality?->en
                ?? $partner->profession?->ar
                ?? $partner->profession?->fr
                ?? $partner->profession?->en
                ?? 'أخصائي';

            $imageUrl = $this->formatImageUrl($partner->user?->image_url);
            $phone = $partner->phone_public ?? $partner->user?->phone_number;

            $markers[] = [
                'id' => (int) $partner->id,
                'lat' => $partner->lat ? (float) $partner->lat : 36.7538,
                'lng' => $partner->lng ? (float) $partner->lng : 3.0588,
                'title' => $title,
                'name' => $title,
                'address' => $partner->address ?? $communeLabel ?? 'الجزائر',
                'speciality' => $specialityName,
                'phone' => $phone,
                'phone_number' => $phone,
                'image_url' => $imageUrl,
                'profile_pic' => $imageUrl,
                'is_on_duty' => (bool) $partner->is_on_duty,
                'rating' => 4.8,
                'reviews_count' => 120,
                'profession' => [
                    'code' => $code,
                    'label' => $label,
                    'hex' => $pinColor,
                ],
                'partner_type' => [
                    'code' => $code,
                    'label' => $label,
                    'hex' => $pinColor,
                ],
                'pin_color' => $pinColor,
            ];
        }

        // Dynamic filters from Profession
        $professions = Profession::all();

        $filters = [
            [
                'key' => 'all',
                'label' => 'الكل',
                'count' => count($markers),
            ],
        ];

        foreach ($professions as $prof) {
            $code = $prof->code;
            $label = $prof->ar ?? $prof->fr ?? $prof->en ?? $code;
            $count = count(array_filter($markers, fn ($m) => ($m['profession']['code'] ?? '') === $code));

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

    /**
     * Format image URL to always return an absolute/full URL.
     */
    private function formatImageUrl(?string $imageUrl): ?string
    {
        if (! $imageUrl) {
            return null;
        }

        if (str_starts_with($imageUrl, 'http://') || str_starts_with($imageUrl, 'https://')) {
            return $imageUrl;
        }

        return url($imageUrl);
    }
}
