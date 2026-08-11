<?php

namespace App\Http\Controllers\V1\Api\Patient;

use App\Http\Controllers\Controller;
use App\Models\Center;
use App\Models\Pharmacy;
use App\Models\Professional;
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

        // 1. Fetch professionals
        if (! $entityType || $entityType === 'professional' || $entityType === 'all') {
            $profQuery = Professional::with(['user', 'specialty'])->where('is_available', true);
            if ($wilayaCode) {
                $profQuery->where('wilaya_code', $wilayaCode);
            }
            $professionals = $profQuery->limit($limit)->get();
            foreach ($professionals as $index => $professional) {
                $markers[] = $professional->formatMapMarker($index);
            }
        }

        // 2. Fetch centers
        if (! $entityType || $entityType === 'center' || $entityType === 'all') {
            $centerQuery = Center::with(['user'])->where('is_active', true);
            if ($wilayaCode) {
                $centerQuery->where('wilaya_code', $wilayaCode);
            }
            $centers = $centerQuery->limit($limit)->get();
            foreach ($centers as $index => $center) {
                $markers[] = $center->formatMapMarker($index);
            }
        }

        // 3. Fetch pharmacies
        if (! $entityType || $entityType === 'pharmacy' || $entityType === 'all') {
            $pharmacyQuery = Pharmacy::with(['user'])->where('is_available', true);
            if ($wilayaCode) {
                $pharmacyQuery->where('wilaya_code', $wilayaCode);
            }
            $pharmacies = $pharmacyQuery->limit($limit)->get();
            foreach ($pharmacies as $index => $pharmacy) {
                $markers[] = $pharmacy->formatMapMarker($index);
            }
        }

        // 4. Selected card (defaults to the first marker)
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
