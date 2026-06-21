<?php

namespace App\Http\Controllers\Api\Doctor\M2;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingHistory;
use App\Models\Doctor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class M2DoctorController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tab = $request->query('tab', 'pending');
        $doctor = null;
        $user = $request->user();

        if ($user) {
            $doctor = Doctor::where('user_id', $user->id)->first();
        }

        if (!$doctor) {
            $doctor = Doctor::first();
        }

        if (!$doctor) {
            return response()->json([
                'tabs' => [
                    ['key' => 'pending', 'label' => 'قيد الانتظار', 'count' => 0],
                    ['key' => 'confirmed', 'label' => 'مؤكدة', 'count' => 0],
                    ['key' => 'previous', 'label' => 'سابقة', 'count' => 0],
                ],
                'appointments' => [],
            ]);
        }

        // Tab counts
        $pendingCount = Booking::where('bookable_type', Doctor::class)
            ->where('bookable_id', $doctor->id)
            ->where('status_code', 'pending')
            ->count();

        $confirmedCount = Booking::where('bookable_type', Doctor::class)
            ->where('bookable_id', $doctor->id)
            ->where('status_code', 'confirmed')
            ->count();

        $previousCount = Booking::where('bookable_type', Doctor::class)
            ->where('bookable_id', $doctor->id)
            ->whereIn('status_code', ['completed', 'cancelled', 'no_show'])
            ->count();

        // Query appointments for current tab
        $query = Booking::with(['doctorService.serviceCatalog'])
            ->where('bookable_type', Doctor::class)
            ->where('bookable_id', $doctor->id);

        if ($tab === 'confirmed') {
            $query->where('status_code', 'confirmed');
        } elseif ($tab === 'previous') {
            $query->whereIn('status_code', ['completed', 'cancelled', 'no_show']);
        } else {
            $query->where('status_code', 'pending');
        }

        $bookings = $query->orderBy('booking_date', 'asc')
            ->orderBy('booking_time', 'asc')
            ->get();

        $appointments = [];
        foreach ($bookings as $booking) {
            $statusLabel = 'مؤكد';
            if ($booking->status_code === 'pending') {
                $statusLabel = 'قيد الانتظار';
            } elseif ($booking->status_code === 'cancelled') {
                $statusLabel = 'ملغي';
            } elseif ($booking->status_code === 'completed') {
                $statusLabel = 'مكتمل';
            } elseif ($booking->status_code === 'no_show') {
                $statusLabel = 'لم يحضر';
            }

            $appointments[] = [
                'id' => $booking->id,
                'reference' => $booking->reference,
                'patient_name' => $booking->patient_name ?? 'مريض',
                'date' => Carbon::parse($booking->booking_date)->format('Y-m-d'),
                'time' => Carbon::parse($booking->booking_time)->format('H:i'),
                'visit_type' => $booking->doctorService?->serviceCatalog?->ar ?? $booking->doctorService?->serviceCatalog?->en ?? 'استشارة',
                'status' => $statusLabel,
                'status_key' => $booking->status_code,
                'proposed_date' => $booking->proposed_date ? Carbon::parse($booking->proposed_date)->format('Y-m-d') : null,
                'proposed_time' => $booking->proposed_time ? Carbon::parse($booking->proposed_time)->format('H:i') : null,
                'has_pending_proposal' => (bool)$booking->has_pending_proposal,
                'can_confirm' => ($booking->status_code === 'pending' && !$booking->has_pending_proposal),
                'can_reject' => ($booking->status_code === 'pending'),
                'can_suggest_new_time' => ($booking->status_code === 'pending' && !$booking->has_pending_proposal),
            ];
        }

        return response()->json([
            'tabs' => [
                ['key' => 'pending', 'label' => 'قيد الانتظار', 'count' => $pendingCount],
                ['key' => 'confirmed', 'label' => 'مؤكدة', 'count' => $confirmedCount],
                ['key' => 'previous', 'label' => 'سابقة', 'count' => $previousCount],
            ],
            'appointments' => $appointments,
        ]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|string|in:confirmed,rejected',
        ]);

        $booking = Booking::find($id);

        if (!$booking) {
            return response()->json(['error' => 'Booking not found'], 404);
        }

        $statusCode = $validated['status'] === 'confirmed' ? 'confirmed' : 'cancelled';

        $booking->status_code = $statusCode;
        $booking->has_pending_proposal = false; // Reset proposal since status changed
        $booking->save();

        BookingHistory::create([
            'booking_id' => $booking->id,
            'status_code' => $statusCode,
            'notes' => 'Status updated by doctor',
            'changed_by' => $request->user()?->id,
        ]);

        return response()->json([
            'success' => true,
            'booking' => $booking,
        ]);
    }

    public function suggest(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'proposed_date' => 'required|date_format:Y-m-d',
            'proposed_time' => 'required|string',
        ]);

        $booking = Booking::find($id);

        if (!$booking) {
            return response()->json(['error' => 'Booking not found'], 404);
        }

        $booking->proposed_date = $validated['proposed_date'];
        $booking->proposed_time = $validated['proposed_time'];
        $booking->has_pending_proposal = true;
        $booking->save();

        BookingHistory::create([
            'booking_id' => $booking->id,
            'status_code' => $booking->status_code,
            'notes' => 'Reschedule suggested by doctor: ' . $validated['proposed_date'] . ' ' . $validated['proposed_time'],
            'changed_by' => $request->user()?->id,
        ]);

        return response()->json([
            'success' => true,
            'booking' => $booking,
        ]);
    }
}
