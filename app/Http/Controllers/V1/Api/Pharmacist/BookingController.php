<?php

namespace App\Http\Controllers\V1\Api\Pharmacist;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    /**
     * Get list of pharmacist bookings (Stub placeholder).
     */
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'tabs' => [
                ['key' => 'new', 'label' => 'جديدة', 'count' => 0],
                ['key' => 'today', 'label' => 'اليوم', 'count' => 0],
                ['key' => 'all', 'label' => 'الكل', 'count' => 0],
            ],
            'bookings' => [],
        ]);
    }

    /**
     * Update booking status (Stub placeholder).
     */
    public function update(Request $request, int $id): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Pharmacist bookings are not supported under the current database schema.',
        ], 400);
    }

    /**
     * Propose reschedule options (Stub placeholder).
     */
    public function suggest(Request $request, int $id): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Pharmacist bookings are not supported under the current database schema.',
        ], 400);
    }
}
