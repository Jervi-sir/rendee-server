<?php

namespace App\Http\Controllers\Api\Patient\Center;

use App\Http\Controllers\Controller;
use App\Models\Center;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PatientCenterController extends Controller
{
    public function show(Request $request, int $id): JsonResponse
    {
        $center = Center::with(['catalog', 'services.serviceCatalog', 'user'])->find($id);

        if (!$center) {
            return response()->json([
                'message' => 'Center not found.'
            ], 404);
        }

        $services = $center->services->map(function ($s) {
            return [
                'id' => $s->id,
                'name' => $s->serviceCatalog?->ar ?? $s->serviceCatalog?->en ?? 'خدمة طبية',
                'price' => $s->price,
                'duration_minutes' => $s->duration_minutes,
            ];
        })->all();

        return response()->json([
            'center' => [
                'id' => $center->id,
                'name' => $center->name ?? $center->user?->name ?? 'مركز طبي',
                'type' => $center->catalog?->ar ?? $center->catalog?->en ?? 'مركز طبي',
                'description' => $center->description,
                'rating' => '4.8',
                'reviews_count' => 127,
                'emergency_label' => $center->emergency_24_7 ? 'متاح' : 'غير متاح',
                'distance' => '1.2 كم',
                'address' => $center->address,
                'city' => $center->city,
                'phone' => $center->phone_public,
                'working_hours' => '٨:٠٠ ص - ١٠:٠٠ م',
                'services' => $services,
            ]
        ]);
    }
}
