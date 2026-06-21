<?php

namespace App\Http\Controllers\Api\Patient\Nearby;

use App\Http\Controllers\Controller;
use App\Models\Center;
use App\Models\Doctor;
use App\Models\Pharmacist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PatientNearbyController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $markers = [];

        // Fetch doctors
        $doctors = Doctor::with(['user', 'specialty'])->where('is_available', true)->limit(10)->get();
        foreach ($doctors as $index => $doctor) {
            $markers[] = [
                'id' => $doctor->id,
                'title' => 'د. ' . ($doctor->user->full_name ?? $doctor->user->name ?? ''),
                'latitude' => $doctor->latitude ? (float) $doctor->latitude : (35.6971 + 0.005 * ($index - 2)),
                'longitude' => $doctor->longitude ? (float) $doctor->longitude : (-0.6308 + 0.005 * ($index - 2)),
                'entity_type' => 'doctor',
                'entity_id' => $doctor->id,
            ];
        }

        // Fetch centers
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

        // Fetch pharmacists
        $pharmacists = Pharmacist::where('is_active', true)->limit(10)->get();
        foreach ($pharmacists as $index => $pharmacist) {
            $markers[] = [
                'id' => $pharmacist->id,
                'title' => $pharmacist->name,
                'latitude' => $pharmacist->latitude ? (float) $pharmacist->latitude : (35.6971 + 0.003 * ($index - 3)),
                'longitude' => $pharmacist->longitude ? (float) $pharmacist->longitude : (-0.6308 + 0.003 * ($index - 3)),
                'entity_type' => 'pharmacist',
                'entity_id' => $pharmacist->id,
            ];
        }

        // Selected card (default to the first marker)
        $selectedCard = null;

        if (!empty($markers)) {
            $first = $markers[0];

            $city = 'وهران';
            $address = 'بير الجير';

            if ($first['entity_type'] === 'doctor') {
                $doc = Doctor::find($first['entity_id']);
                if ($doc) {
                    $city = $doc->city ?? $city;
                    $address = $doc->address ? substr($doc->address, 0, 25) : $address;
                }
            } elseif ($first['entity_type'] === 'center') {
                $cen = Center::find($first['entity_id']);
                if ($cen) {
                    $city = $cen->city ?? $city;
                    $address = $cen->address ? substr($cen->address, 0, 25) : $address;
                }
            } else {
                $ph = Pharmacist::find($first['entity_id']);
                if ($ph) {
                    $city = $ph->city ?? $city;
                    $address = $ph->address ? substr($ph->address, 0, 25) : $address;
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
