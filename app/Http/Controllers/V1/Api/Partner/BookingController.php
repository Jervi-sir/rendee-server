<?php

namespace App\Http\Controllers\V1\Api\Partner;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingHistory;
use App\Models\Partner;
use App\Services\Notification\BookingNotificationService;
use App\Support\TimeHelper;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    public function __construct(
        protected BookingNotificationService $bookingNotificationService
    ) {}
    /**
     * Get list of appointments for the partner, grouped by tabs.
     */
    public function index(Request $request): JsonResponse
    {
        $tab = $request->query('tab', 'all');
        $partner = null;
        $user = $request->user();

        if ($user) {
            $partner = Partner::where('user_id', $user->id)->first();
        }

        if (! $partner) {
            $partner = Partner::first();
        }

        if (! $partner) {
            return response()->json([
                'tabs' => [
                    ['key' => 'pending', 'label' => 'قيد الانتظار', 'count' => 0],
                    ['key' => 'confirmed', 'label' => 'مؤكدة', 'count' => 0],
                    ['key' => 'previous', 'label' => 'سابقة', 'count' => 0],
                ],
                'appointments' => [],
            ]);
        }

        // Tab & Patients counts
        $pendingCount = Booking::where('partner_id', $partner->id)
            ->where('status_code', 'pending')
            ->count();

        $confirmedCount = Booking::where('partner_id', $partner->id)
            ->where('status_code', 'confirmed')
            ->count();

        $previousCount = Booking::where('partner_id', $partner->id)
            ->whereIn('status_code', ['completed', 'cancelled', 'no_show'])
            ->count();

        $patientsCount = Booking::where('partner_id', $partner->id)
            ->whereNotNull('patient_id')
            ->distinct('patient_id')
            ->count('patient_id');

        // Query appointments for current tab
        $query = Booking::with(['patient', 'service', 'status'])
            ->where('partner_id', $partner->id);

        if ($tab === 'confirmed') {
            $query->where('status_code', 'confirmed');
        } elseif ($tab === 'previous') {
            $query->whereIn('status_code', ['completed', 'cancelled', 'no_show']);
        } elseif ($tab === 'pending') {
            $query->where('status_code', 'pending');
        }

        if ($tab === 'previous') {
            $query->orderBy('booking_date', 'desc')
                ->orderBy('booking_time', 'desc');
        } else {
            $query->orderBy('booking_date', 'asc')
                ->orderBy('booking_time', 'asc');
        }

        $bookings = $query->get();

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
                'visit_type' => $booking->service?->catalog?->ar ?? $booking->service?->catalog?->en ?? $booking->service?->name ?? 'استشارة',
                'price' => $booking->service?->price ?? null,
                'status' => $statusLabel,
                'status_key' => $booking->status_code,
                'proposed_date' => $booking->proposed_date ? Carbon::parse($booking->proposed_date)->format('Y-m-d') : null,
                'proposed_time' => $booking->proposed_time ? Carbon::parse($booking->proposed_time)->format('H:i') : null,
                'has_pending_proposal' => (bool) $booking->has_pending_proposal,
                'can_confirm' => ($booking->status_code === 'pending' && ! $booking->has_pending_proposal),
                'can_reject' => in_array($booking->status_code, ['pending', 'confirmed']),
                'can_cancel' => in_array($booking->status_code, ['pending', 'confirmed']),
                'can_complete' => ($booking->status_code === 'confirmed'),
                'can_suggest_new_time' => in_array($booking->status_code, ['pending', 'confirmed']),
                'can_follow_up' => in_array($booking->status_code, ['confirmed', 'completed']),
            ];
        }

        return response()->json([
            'tabs' => [
                ['key' => 'pending', 'label' => 'قيد الانتظار', 'count' => $pendingCount],
                ['key' => 'confirmed', 'label' => 'مؤكدة', 'count' => $confirmedCount],
                ['key' => 'previous', 'label' => 'سابقة', 'count' => $previousCount],
            ],
            'patients_count' => $patientsCount,
            'appointments' => $appointments,
        ]);
    }

    /**
     * Get single booking details for the partner.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $partner = null;

        if ($user) {
            $partner = Partner::where('user_id', $user->id)->first();
        }

        $booking = Booking::with(['service.catalog', 'patient.user', 'status'])->where('id', $id);
        if ($partner) {
            $booking->where('partner_id', $partner->id);
        }
        $booking = $booking->first();

        if (! $booking) {
            return response()->json(['error' => 'Booking not found'], 404);
        }

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

        $serviceName = $booking->service?->catalog?->ar
          ?? $booking->service?->catalog?->en
          ?? $booking->service?->name
          ?? 'استشارة';

        return response()->json([
            'success' => true,
            'appointment' => [
                'id' => $booking->id,
                'patient_id' => $booking->patient_id,
                'reference' => $booking->reference,
                'patient_name' => $booking->patient_name ?? $booking->patient?->user?->full_name ?? $booking->patient?->user?->name ?? 'مريض',
                'patient_phone' => $booking->patient_phone ?? $booking->patient?->user?->phone_number ?? '',
                'patient_email' => $booking->patient?->user?->email ?? '',
                'date' => $booking->booking_date ? (is_string($booking->booking_date) ? $booking->booking_date : $booking->booking_date->format('Y-m-d')) : '',
                'time' => $booking->booking_time ? Carbon::parse($booking->booking_time)->format('H:i') : '',
                'visit_type' => $serviceName,
                'service_name' => $serviceName,
                'price' => $booking->service?->price ?? null,
                'status' => $booking->status_code,
                'status_label' => $statusLabel,
                'status_key' => $booking->status_code,
                'proposed_date' => $booking->proposed_date ? (is_string($booking->proposed_date) ? $booking->proposed_date : $booking->proposed_date->format('Y-m-d')) : null,
                'proposed_time' => $booking->proposed_time ? Carbon::parse($booking->proposed_time)->format('H:i') : null,
                'has_pending_proposal' => (bool) $booking->has_pending_proposal,
                'can_confirm' => ($booking->status_code === 'pending' && ! $booking->has_pending_proposal),
                'can_reject' => in_array($booking->status_code, ['pending', 'confirmed']),
                'can_cancel' => in_array($booking->status_code, ['pending', 'confirmed']),
                'can_complete' => ($booking->status_code === 'confirmed'),
                'can_suggest_new_time' => in_array($booking->status_code, ['pending', 'confirmed']),
                'can_follow_up' => in_array($booking->status_code, ['confirmed', 'completed']),
                'notes' => $booking->notes,
            ],
        ]);
    }

    /**
     * Update booking status (confirm, complete, cancel, reject).
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:confirmed,completed,cancelled,rejected,no_show'],
            'notes' => ['nullable', 'string'],
        ]);

        $user = $request->user();
        $partner = null;

        if ($user) {
            $partner = Partner::where('user_id', $user->id)->first();
        }

        $booking = Booking::where('id', $id);
        if ($partner) {
            $booking->where('partner_id', $partner->id);
        }
        $booking = $booking->first();

        if (! $booking) {
            return response()->json(['error' => 'Booking not found'], 404);
        }

        $statusCode = match ($validated['status']) {
            'rejected' => 'cancelled',
            default => $validated['status'],
        };

        $booking->status_code = $statusCode;
        $booking->has_pending_proposal = false;
        $booking->save();

        BookingHistory::create([
            'booking_id' => $booking->id,
            'status_code' => $statusCode,
            'notes' => $validated['notes'] ?? 'Status updated to '.$statusCode.' by partner',
            'changed_by' => $user?->id,
        ]);

        $booking->load(['patient.user', 'partner.user']);

        try {
            if ($statusCode === Booking::STATUS_CONFIRMED) {
                $this->bookingNotificationService->notifyBookingConfirmed($booking);
            } elseif ($statusCode === Booking::STATUS_CANCELLED) {
                $this->bookingNotificationService->notifyBookingCancelled($booking, reason: $validated['notes'] ?? null, cancelledBy: 'partner');
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to dispatch status update notification', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully.',
            'booking' => $booking,
        ]);
    }

    /**
     * Mark an appointment as completed.
     */
    public function complete(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'notes' => ['nullable', 'string'],
        ]);

        $user = $request->user();
        $partner = null;

        if ($user) {
            $partner = Partner::where('user_id', $user->id)->first();
        }

        $booking = Booking::where('id', $id);
        if ($partner) {
            $booking->where('partner_id', $partner->id);
        }
        $booking = $booking->first();

        if (! $booking) {
            return response()->json(['error' => 'Booking not found'], 404);
        }

        $booking->status_code = 'completed';
        $booking->has_pending_proposal = false;
        $booking->save();

        BookingHistory::create([
            'booking_id' => $booking->id,
            'status_code' => 'completed',
            'notes' => $validated['notes'] ?? 'Appointment marked as completed by partner',
            'changed_by' => $user?->id,
        ]);

        $booking->load(['patient.user', 'partner.user']);

        try {
            $this->bookingNotificationService->notifyBookingCompleted($booking);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to dispatch complete notification', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Appointment marked as completed successfully.',
            'booking' => $booking,
        ]);
    }

    /**
     * Cancel an appointment.
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'reason' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $user = $request->user();
        $partner = null;

        if ($user) {
            $partner = Partner::where('user_id', $user->id)->first();
        }

        $booking = Booking::where('id', $id);
        if ($partner) {
            $booking->where('partner_id', $partner->id);
        }
        $booking = $booking->first();

        if (! $booking) {
            return response()->json(['error' => 'Booking not found'], 404);
        }

        $booking->status_code = 'cancelled';
        $booking->has_pending_proposal = false;
        $booking->save();

        $note = $validated['reason'] ?? $validated['notes'] ?? 'Appointment cancelled by partner';

        BookingHistory::create([
            'booking_id' => $booking->id,
            'status_code' => 'cancelled',
            'notes' => $note,
            'changed_by' => $user?->id,
        ]);

        $booking->load(['patient.user', 'partner.user']);

        try {
            $this->bookingNotificationService->notifyBookingCancelled($booking, reason: $note, cancelledBy: 'partner');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to dispatch cancel notification', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Appointment cancelled successfully.',
            'booking' => $booking,
        ]);
    }

    /**
     * Propose alternative date/time for an appointment (reschedule).
     */
    public function suggest(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'proposed_date' => ['required', 'date_format:Y-m-d'],
            'proposed_time' => ['required', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $user = $request->user();
        $partner = null;

        if ($user) {
            $partner = Partner::where('user_id', $user->id)->first();
        }

        $booking = Booking::where('id', $id);
        if ($partner) {
            $booking->where('partner_id', $partner->id);
        }
        $booking = $booking->first();

        if (! $booking) {
            return response()->json(['error' => 'Booking not found'], 404);
        }

        $normalizedProposedTime = TimeHelper::normalize($validated['proposed_time']) ?? $validated['proposed_time'];
        $booking->proposed_date = $validated['proposed_date'];
        $booking->proposed_time = $normalizedProposedTime;
        $booking->has_pending_proposal = true;
        $booking->save();

        $note = $validated['notes'] ?? 'Reschedule suggested by partner: '.$validated['proposed_date'].' '.$normalizedProposedTime;

        BookingHistory::create([
            'booking_id' => $booking->id,
            'status_code' => $booking->status_code,
            'notes' => $note,
            'changed_by' => $user?->id,
        ]);

        $booking->load(['patient.user', 'partner.user']);

        try {
            $this->bookingNotificationService->notifyProposalSent($booking);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to dispatch proposal notification', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'New appointment date/time suggested successfully.',
            'booking' => $booking,
        ]);
    }

    /**
     * Create a second follow-up booking for the same patient.
     */
    public function followUp(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'booking_date' => ['required', 'date_format:Y-m-d'],
            'booking_time' => ['required', 'string'],
            'service_id' => ['nullable', 'integer'],
            'notes' => ['nullable', 'string'],
            'status' => ['nullable', 'string', 'in:confirmed,pending'],
        ]);

        $user = $request->user();
        $partner = null;

        if ($user) {
            $partner = Partner::where('user_id', $user->id)->first();
        }

        $booking = Booking::where('id', $id);
        if ($partner) {
            $booking->where('partner_id', $partner->id);
        }
        $booking = $booking->first();

        if (! $booking) {
            return response()->json(['error' => 'Original booking not found'], 404);
        }

        $normalizedTime = TimeHelper::normalize($validated['booking_time']) ?? $validated['booking_time'];
        $reference = 'BK-'.strtoupper(Str::random(8));

        $serviceId = $validated['service_id'] ?? $booking->partner_service_id;
        $statusCode = $validated['status'] ?? 'confirmed';

        $newBooking = Booking::create([
            'reference' => $reference,
            'patient_id' => $booking->patient_id,
            'partner_id' => $booking->partner_id,
            'partner_service_id' => $serviceId,
            'patient_name' => $booking->patient_name,
            'patient_phone' => $booking->patient_phone,
            'booking_date' => $validated['booking_date'],
            'booking_time' => $normalizedTime,
            'status_code' => $statusCode,
            'is_center' => (bool) $booking->is_center,
            'has_pending_proposal' => false,
            'notes' => $validated['notes'] ?? 'موعد متابعة (Follow-up appointment)',
        ]);

        BookingHistory::create([
            'booking_id' => $newBooking->id,
            'status_code' => $statusCode,
            'notes' => 'Follow-up booking created from original booking #'.$booking->reference,
            'changed_by' => $user?->id,
        ]);

        $newBooking->load(['service.serviceCatalog', 'patient.user', 'status']);

        return response()->json([
            'success' => true,
            'message' => 'Follow-up appointment created successfully.',
            'booking' => $newBooking,
        ], 201);
    }
}
