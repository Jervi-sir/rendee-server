<?php

namespace App\Http\Controllers\V1\Api\Patient;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PharmacistController extends Controller
{
    /**
     * Display a listing of pharmacies.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Pharmacy::with(['user']);

        if ($request->has('wilaya_code')) {
            $query->where('wilaya_code', $request->query('wilaya_code'));
        }

        if ($request->has('city')) {
            $query->where('location', 'like', '%' . $request->query('city') . '%');
        }

        $pharmacies = $query->where('is_available', true)->get()->map(function ($pharmacy) {
            return $pharmacy->formatForPatient(false);
        });

        return response()->json([
            'pharmacies' => $pharmacies,
        ]);
    }

    /**
     * Display detailed profile for a specific pharmacy.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $pharmacy = Pharmacy::with(['user'])->find($id);

        if (!$pharmacy) {
            return response()->json([
                'message' => 'Pharmacy not found.',
            ], 404);
        }

        return response()->json([
            'pharmacy' => $pharmacy->formatForPatient(true),
        ]);
    }
}
