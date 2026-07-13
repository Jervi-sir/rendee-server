<?php

namespace App\Http\Controllers\V1\Api\Patient;

use App\Http\Controllers\Controller;
use App\Models\Center;
use App\Models\Professional;
use App\Models\Pharmacy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MapController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $markers = [];

        // 1. Fetch professionals
        $professionals = Professional::with(['user', 'specialty'])->where('is_available', true)->limit(10)->get();
        foreach ($professionals as $index => $professional) {
            $markers[] = [
                'id' => $professional->id,
                'title' => ($professional->profession_code === 'doctor' ? 'د. ' : '') . ($professional->user->full_name ?? $professional->user->name ?? ''),
                'latitude' => $professional->latitude ? (float) $professional->latitude : (35.6971 + 0.005 * ($index - 2)),
                'longitude' => $professional->longitude ? (float) $professional->longitude : (-0.6308 + 0.005 * ($index - 2)),
                'entity_type' => 'professional',
                'entity_id' => $professional->id,
            ];
        }

        // 2. Fetch centers
        $centers = Center::with(['user'])->where('is_active', true)->limit(10)->get();
        foreach ($centers as $index => $center) {
            $markers[] = [
                'id' => $center->id,
                'title' => $center->name ?? $center->user->full_name ?? $center->user->name ?? '',
                'latitude' => $center->latitude ? (float) $center->latitude : (35.6971 + 0.004 * ($index - 1)),
                'longitude' => $center->longitude ? (float) $center->longitude : (-0.6308 - 0.004 * ($index - 1)),
                'entity_type' => 'center',
                'entity_id' => $center->id,
            ];
        }

        // 3. Fetch pharmacies
        $pharmacies = Pharmacy::with(['user'])->where('is_available', true)->limit(10)->get();
        foreach ($pharmacies as $index => $pharmacy) {
            $markers[] = [
                'id' => $pharmacy->id,
                'title' => $pharmacy->name ?? $pharmacy->user?->name ?? 'صيدلية',
                'latitude' => $pharmacy->latitude ? (float) $pharmacy->latitude : (35.6971 + 0.003 * ($index - 3)),
                'longitude' => $pharmacy->longitude ? (float) $pharmacy->longitude : (-0.6308 + 0.003 * ($index - 3)),
                'entity_type' => 'pharmacy',
                'entity_id' => $pharmacy->id,
            ];
        }

        // 4. Selected card (defaults to the first marker)
        $selectedCard = null;

        if (!empty($markers)) {
            $first = $markers[0];
            $city = 'وهران';
            $address = 'بير الجير';

            if ($first['entity_type'] === 'professional') {
                $prof = Professional::find($first['entity_id']);
                if ($prof) {
                    $city = $prof->city ?? $city;
                    $address = $prof->address ? substr($prof->address, 0, 25) : $address;
                }
            } elseif ($first['entity_type'] === 'center') {
                $cen = Center::find($first['entity_id']);
                if ($cen) {
                    $city = $cen->city ?? $city;
                    $address = $cen->address ? substr($cen->address, 0, 25) : $address;
                }
            } elseif ($first['entity_type'] === 'pharmacy') {
                $ph = Pharmacy::find($first['entity_id']);
                if ($ph) {
                    $city = $ph->city ?? $city;
                    $address = $ph->location ? substr($ph->location, 0, 25) : $address;
                }
            }

            $selectedCard = [
                'title' => $first['title'],
                'subtitle' => $city . '، ' . $address . ' (2.4 كم)',
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
