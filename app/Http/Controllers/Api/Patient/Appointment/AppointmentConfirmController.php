<?php

namespace App\Http\Controllers\Api\Patient\Appointment;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Center;
use App\Models\Doctor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AppointmentConfirmController extends Controller
{
    public function show(Request $request, $id): JsonResponse
    {
        $booking = Booking::with([
            'centerService.serviceCatalog',
            'doctorService.serviceCatalog',
            'bookable.user'
        ])->where('id', $id)->orWhere('reference', $id)->first();

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found.'
            ], 404);
        }

        $bookableName = 'غير معروف';
        $speciality = 'عام';
        $price = 0;

        if ($booking->bookable_type === Doctor::class) {
            $doctor = Doctor::with(['user', 'specialty'])->find($booking->bookable_id);
            if ($doctor) {
                $bookableName = 'د. ' . ($doctor->user->full_name ?? $doctor->user->name ?? '');
                $speciality = $doctor->specialty?->ar ?? $doctor->specialty?->en ?? 'طبيب عام';
            }
            $price = $booking->doctorService?->price ?? 2000;
        } else if ($booking->bookable_type === Center::class) {
            $center = Center::with(['user', 'catalog'])->find($booking->bookable_id);
            if ($center) {
                $bookableName = $center->name ?? $center->user->full_name ?? $center->user->name ?? '';
                $speciality = $center->catalog?->ar ?? $center->catalog?->en ?? 'مركز طبي';
            }
            $price = $booking->centerService?->price ?? 1500;
        }

        // Format booking date
        $dateObj = Carbon::parse($booking->booking_date);
        $formattedDate = $dateObj->translatedFormat('l، d F Y') ?? $dateObj->format('Y-m-d');
        $formattedTime = Carbon::parse($booking->booking_time)->format('H:i');

        return response()->json([
            'success' => true,
            'booking' => [
                'id' => $booking->id,
                'reference' => $booking->reference,
                'bookable_name' => $bookableName,
                'speciality' => $speciality,
                'date' => $formattedDate,
                'time' => $formattedTime,
                'patient_name' => $booking->patient_name ?? 'مريض',
                'patient_phone' => $booking->patient_phone ?? '0555000000',
                'price' => (float)$price,
            ]
        ]);
    }

    public function acceptProposal(Request $request, $id): JsonResponse
    {
        $booking = Booking::find($id);

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found.'
            ], 404);
        }

        if (!$booking->has_pending_proposal) {
            return response()->json([
                'success' => false,
                'message' => 'No pending proposal found for this booking.'
            ], 400);
        }

        $booking->booking_date = $booking->proposed_date;
        $booking->booking_time = $booking->proposed_time;
        $booking->proposed_date = null;
        $booking->proposed_time = null;
        $booking->has_pending_proposal = false;
        $booking->status_code = 'confirmed';
        $booking->save();

        return response()->json([
            'success' => true,
            'message' => 'Proposal accepted successfully.',
            'booking' => $booking
        ]);
    }

    public function rejectProposal(Request $request, $id): JsonResponse
    {
        $booking = Booking::find($id);

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found.'
            ], 404);
        }

        if (!$booking->has_pending_proposal) {
            return response()->json([
                'success' => false,
                'message' => 'No pending proposal found for this booking.'
            ], 400);
        }

        $booking->proposed_date = null;
        $booking->proposed_time = null;
        $booking->has_pending_proposal = false;
        $booking->status_code = 'cancelled';
        $booking->save();

        return response()->json([
            'success' => true,
            'message' => 'Proposal rejected successfully.',
            'booking' => $booking
        ]);
    }
}
