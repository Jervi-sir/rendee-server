<?php

namespace App\Http\Controllers\V1\Api\Partner;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Partner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    /**
     * Get agenda bookings and appointment counts by date for Partners.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $partner = null;

        if ($user) {
            $partner = Partner::where('user_id', $user->id)->first();
        }

        if (! $partner) {
            $partner = Partner::first();
        }

        if (! $partner) {
            return response()->json([
                'success' => true,
                'message' => 'Agenda bookings retrieved successfully.',
                'data' => [
                    'bookings' => [],
                    'counts_by_date' => (object) [],
                ],
            ]);
        }

        // Optional date filter parameters
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $month = $request->query('month');

        $query = Booking::with(['patient.user', 'service.catalog'])
            ->where('partner_id', $partner->id);

        if ($startDate && $endDate) {
            $query->whereBetween('booking_date', [$startDate, $endDate]);
        } elseif ($month) {
            $query->where('booking_date', 'like', "{$month}%");
        }

        $bookings = $query->orderBy('booking_date', 'asc')
            ->orderBy('booking_time', 'asc')
            ->get();

        $formattedBookings = [];
        $countsByDate = [];

        foreach ($bookings as $booking) {
            $dateStr = $booking->booking_date ? (is_string($booking->booking_date) ? $booking->booking_date : $booking->booking_date->format('Y-m-d')) : null;

            if ($dateStr) {
                $countsByDate[$dateStr] = ($countsByDate[$dateStr] ?? 0) + 1;
            }

            // Patient details fallback logic
            $patientName = $booking->patient_name
                ?? $booking->patient?->user?->full_name
                ?? $booking->patient?->user?->name
                ?? 'مريض';

            $patientPhone = $booking->patient_phone
                ?? $booking->patient?->user?->phone_number
                ?? null;

            $serviceFormatted = null;
            if ($booking->service) {
                $serviceName = $booking->service->catalog?->ar
                    ?? $booking->service->catalog?->en
                    ?? $booking->service->name
                    ?? 'استشارة';
                $serviceFormatted = [
                    'id' => $booking->service->id,
                    'name' => $serviceName,
                    'price' => $booking->service->price ?? null,
                    'duration_minutes' => $booking->service->duration_minutes ?? null,
                ];
            }

            $formattedBookings[] = [
                'id' => $booking->id,
                'reference' => $booking->reference,
                'patient_id' => $booking->patient_id,
                'partner_id' => $booking->partner_id,
                'patient_name' => $patientName,
                'patient_phone' => $patientPhone,
                'service' => $serviceFormatted,
                'service_name' => $serviceFormatted ? $serviceFormatted['name'] : 'استشارة',
                'price' => $booking->service?->price ?? null,
                'booking_date' => $dateStr,
                'booking_time' => $booking->booking_time,
                'status_code' => $booking->status_code,
                'is_center' => (bool) $booking->is_center,
                'proposed_date' => $booking->proposed_date ? (is_string($booking->proposed_date) ? $booking->proposed_date : $booking->proposed_date->format('Y-m-d')) : null,
                'proposed_time' => $booking->proposed_time,
                'has_pending_proposal' => (bool) $booking->has_pending_proposal,
                'notes' => $booking->notes,
                'created_at' => $booking->created_at?->toIso8601String(),
                'updated_at' => $booking->updated_at?->toIso8601String(),
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'Agenda bookings retrieved successfully.',
            'data' => [
                'bookings' => $formattedBookings,
                'counts_by_date' => (object) $countsByDate,
            ],
        ]);
    }
}
