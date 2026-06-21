<?php

namespace App\Http\Controllers\Api\Patient\M3;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Center;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class M3PatientController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $statusColors = [
            'pending' => '#ed6c02',
            'confirmed' => '#2e7d32',
            'completed' => '#1565c0',
            'cancelled' => '#d32f2f',
        ];
        $mappedBookings = [];

        $user = $request->user();
        if ($user && $user->user_role_code === 'patient') {
            $patient = $user->patient;
            if (!$patient) {
                $patient = Patient::create(['user_id' => $user->id]);
            }

            $bookings = Booking::with([
                'status',
                'bookable'
            ])
                ->where('patient_id', $patient->id)
                ->orderBy('booking_date', 'desc')
                ->orderBy('booking_time', 'desc')
                ->limit(10)
                ->get();

            foreach ($bookings as $booking) {
                $name = '';
                $specialty = '';

                if ($booking->bookable instanceof Doctor) {
                    $doctor = $booking->bookable;
                    $doctor->load(['user', 'specialty']);
                    $name = 'د. ' . ($doctor->user->full_name ?? $doctor->user->name ?? '');
                    $specialty = $doctor->specialty?->ar ?? $doctor->specialty?->en ?? 'طبيب عام';
                } elseif ($booking->bookable instanceof Center) {
                    $center = $booking->bookable;
                    $center->load(['user', 'catalog']);
                    $name = $center->name ?? $center->user->full_name ?? $center->user->name ?? '';
                    $specialty = $center->catalog?->ar ?? $center->catalog?->en ?? 'مركز طبي';
                }

                $statusCode = $booking->status_code ?? 'pending';
                $statusColor = $statusColors[$statusCode] ?? '#71869b';

                $mappedBookings[] = [
                    'id' => $booking->id,
                    'reference' => $booking->reference ?? ('BK-' . $booking->id),
                    'name' => $name,
                    'specialty' => $specialty,
                    'date' => $booking->booking_date ? $booking->booking_date->format('Y-m-d') : null,
                    'time' => $booking->booking_time ? substr($booking->booking_time, 0, 5) : null,
                    'status' => $booking->status?->ar ?? $booking->status?->en ?? 'قيد الانتظار',
                    'status_color' => $statusColor,
                    'is_center' => (bool)$booking->is_center,
                    'bookable_type' => $booking->bookable_type === Center::class ? 'center' : 'doctor',
                    'bookable_id' => $booking->bookable_id,
                    'has_pending_proposal' => (bool)$booking->has_pending_proposal,
                    'proposed_date' => $booking->proposed_date ? $booking->proposed_date->format('Y-m-d') : null,
                    'proposed_time' => $booking->proposed_time ? substr($booking->proposed_time, 0, 5) : null,
                ];
            }
        }

        return response()->json($mappedBookings);
    }
}
