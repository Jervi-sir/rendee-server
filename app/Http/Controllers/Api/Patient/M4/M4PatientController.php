<?php

namespace App\Http\Controllers\Api\Patient\M4;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\RecentSearch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class M4PatientController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.'
            ], 401);
        }

        $user->load('patient');

        $bookingsCount = Booking::where('patient_id', $user->patient?->id)->count();
        $searchesCount = RecentSearch::where('user_id', $user->id)->count();
        $filesCount = 0;

        return response()->json([
            'user' => [
                'id' => $user->id,
                'full_name' => $user->full_name ?? $user->name,
                'email' => $user->email,
                'phone' => $user->phone_number ?? $user->phone ?? '',
                'profile_complete' => (bool)$user->profile_complete,
            ],
            'bookings_count' => $bookingsCount,
            'searches_count' => $searchesCount,
            'files_count' => $filesCount,
        ]);
    }
}
