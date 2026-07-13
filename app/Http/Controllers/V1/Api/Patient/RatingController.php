<?php

namespace App\Http\Controllers\V1\Api\Patient;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Rating;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    /**
     * Submit a rating and review for a completed booking.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'booking_id' => ['required', 'integer'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'review' => ['nullable', 'string', 'max:1000'],
        ]);

        $user = $request->user();
        $patientId = $user && $user->patient ? $user->patient->id : null;

        if (!$patientId) {
            return response()->json(['error' => 'Patient profile not found'], 403);
        }

        $booking = Booking::where('id', $validated['booking_id'])
            ->where('patient_id', $patientId)
            ->first();

        if (!$booking) {
            return response()->json(['error' => 'Booking not found'], 404);
        }

        // Validate booking status is completed
        if ($booking->status_code !== 'completed') {
            return response()->json([
                'error' => 'You can only rate completed appointments.',
            ], 422);
        }

        // Prevent duplicate ratings for the same booking
        $existing = Rating::where('booking_id', $booking->id)->first();
        if ($existing) {
            return response()->json([
                'error' => 'You have already rated this appointment.',
            ], 422);
        }

        $rating = Rating::create([
            'booking_id' => $booking->id,
            'patient_id' => $patientId,
            'rating' => $validated['rating'],
            'review' => $validated['review'] ?? null,
            'reviewable_type' => $booking->bookable_type,
            'reviewable_id' => $booking->bookable_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Review submitted successfully.',
            'rating' => $rating,
        ], 201);
    }
}
