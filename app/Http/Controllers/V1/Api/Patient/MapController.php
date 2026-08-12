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
        $markers = [];
        $entityType = $request->query('entity_type');
        $wilayaCode = $request->query('wilaya_code');
        $limit = max(1, min(50, (int) $request->query('limit', 10)));

        $query = Partner::with(['user', 'specialty', 'catalog', 'wilaya'])
            ->where('is_active', true)
            ->where('is_available', true);

        if ($entityType && $entityType !== 'all') {
            if (in_array($entityType, ['professional', 'doctor'])) {
                $query->where('partner_type', 'professional');
            } elseif ($entityType === 'center') {
                $query->where('partner_type', 'center');
            } elseif (in_array($entityType, ['pharmacy', 'pharmacist'])) {
                $query->where('partner_type', 'pharmacist');
            }
        }

        if ($wilayaCode) {
            $query->where('wilaya_code', $wilayaCode);
        }

        $partners = $query->limit($limit)->get();

        foreach ($partners as $index => $partner) {
            $markers[] = $partner->formatMapMarker($index);
        }

        // Selected card (defaults to the first marker)
        $selectedCard = null;

        if (! empty($markers)) {
            $first = $markers[0];
            $selectedCard = [
                'title' => $first['title'],
                'subtitle' => $first['city'].'، '.$first['address'].' (2.4 كم)',
                'entity_type' => $first['entity_type'],
                'entity_id' => $first['entity_id'],
                'latitude' => $first['latitude'],
                'longitude' => $first['longitude'],
            ];
        }

        return response()->json([
            'markers' => $markers,
            'selected_card' => $selectedCard,
        ]);
    }
}
