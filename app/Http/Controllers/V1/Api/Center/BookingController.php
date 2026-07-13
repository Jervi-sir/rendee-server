<?php

namespace App\Http\Controllers\V1\Api\Center;

use App\Http\Controllers\Controller;
use App\Models\Center;
use App\Models\Booking;
use App\Models\BookingHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BookingController extends Controller
{
    /**
     * Get list of appointments for the center, grouped by tabs.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $center = null;

        if ($user) {
            $center = Center::where('user_id', $user->id)->first();
        }

        if (!$center) {
            $center = Center::first();
        }

        if (!$center) {
            return response()->json([
                'tabs' => [
                    ['key' => 'new', 'label' => 'جديدة', 'count' => 0],
                    ['key' => 'today', 'label' => 'اليوم', 'count' => 0],
                    ['key' => 'all', 'label' => 'الكل', 'count' => 0],
                ],
                'bookings' => [],
            ]);
        }

        $tab = $request->query('tab', 'new');
        $todayStr = Carbon::today()->format('Y-m-d');

        // Query counts for tabs
        $newCount = Booking::where('bookable_type', Center::class)
            ->where('bookable_id', $center->id)
            ->where('status_code', 'pending')
            ->count();

        $todayCount = Booking::where('bookable_type', Center::class)
            ->where('bookable_id', $center->id)
            ->where('booking_date', $todayStr)
            ->count();

        $allCount = Booking::where('bookable_type', Center::class)
            ->where('bookable_id', $center->id)
            ->count();

        // Query filtered bookings
        $query = Booking::where('bookable_type', Center::class)
            ->where('bookable_id', $center->id)
            ->with(['service.serviceCatalog', 'status']);

        if ($tab === 'new') {
            $query->where('status_code', 'pending');
        } elseif ($tab === 'today') {
            $query->where('booking_date', $todayStr);
        }

        $bookings = $query->orderBy('booking_date', 'asc')
            ->orderBy('booking_time', 'asc')
            ->get();

        $bookingsData = [];
        foreach ($bookings as $b) {
            $statusLabel = $b->status?->ar ?? $b->status?->en ?? $b->status_code;

            $bookingsData[] = [
                'id' => $b->id,
                'reference' => $b->reference,
                'patient_name' => $b->patient_name ?? 'مريض غير معروف',
                'service_name' => $b->service?->serviceCatalog?->ar ?? 'خدمة طبية',
                'date' => $b->booking_date ? Carbon::parse($b->booking_date)->format('Y-m-d') : null,
                'time' => $b->booking_time ? substr($b->booking_time, 0, 5) : null,
                'status' => $statusLabel,
                'status_key' => $b->status_code,
                'proposed_date' => $b->proposed_date ? Carbon::parse($b->proposed_date)->format('Y-m-d') : null,
                'proposed_time' => $b->proposed_time ? Carbon::parse($b->proposed_time)->format('H:i') : null,
                'has_pending_proposal' => (bool) $b->has_pending_proposal,
                'can_confirm' => ($b->status_code === 'pending' && !$b->has_pending_proposal),
                'can_cancel' => in_array($b->status_code, ['pending', 'confirmed']),
                'can_suggest_new_time' => ($b->status_code === 'pending' && !$b->has_pending_proposal),
            ];
        }

        return response()->json([
            'tabs' => [
                ['key' => 'new', 'label' => 'جديدة', 'count' => $newCount],
                ['key' => 'today', 'label' => 'اليوم', 'count' => $todayCount],
                ['key' => 'all', 'label' => 'الكل', 'count' => $allCount],
            ],
            'bookings' => $bookingsData,
        ]);
    }

    /**
     * Update booking status (confirm or cancel).
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:confirmed,cancelled'],
        ]);

        $user = $request->user();
        $center = null;

        if ($user) {
            $center = Center::where('user_id', $user->id)->first();
        }

        $booking = Booking::where('id', $id);
        if ($center) {
            $booking->where('bookable_type', Center::class)
                    ->where('bookable_id', $center->id);
        }
        $booking = $booking->first();

        if (!$booking) {
            return response()->json(['error' => 'Booking not found'], 404);
        }

        $booking->status_code = $validated['status'];
        $booking->has_pending_proposal = false; // Reset proposal since status changed
        $booking->save();

        BookingHistory::create([
            'booking_id' => $booking->id,
            'status_code' => $validated['status'],
            'notes' => 'Status updated by center',
            'changed_by' => $user?->id,
        ]);

        return response()->json([
            'success' => true,
            'booking' => [
                'id' => $booking->id,
                'status_code' => $booking->status_code,
            ],
        ]);
    }

    /**
     * Propose reschedule options (suggest new date/time).
     */
    public function suggest(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'proposed_date' => ['required', 'date_format:Y-m-d'],
            'proposed_time' => ['required', 'string'],
        ]);

        $user = $request->user();
        $center = null;

        if ($user) {
            $center = Center::where('user_id', $user->id)->first();
        }

        $booking = Booking::where('id', $id);
        if ($center) {
            $booking->where('bookable_type', Center::class)
                    ->where('bookable_id', $center->id);
        }
        $booking = $booking->first();

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
            'notes' => 'Reschedule suggested by center: ' . $validated['proposed_date'] . ' ' . $validated['proposed_time'],
            'changed_by' => $user?->id,
        ]);

        return response()->json([
            'success' => true,
            'booking' => $booking,
        ]);
    }
}
