<?php

namespace App\Http\Controllers\V1\Api\Partner;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingHistory;
use App\Models\Professional;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
  /**
   * Get list of appointments for the professional, grouped by tabs.
   */
  public function index(Request $request): JsonResponse
  {
    $tab = $request->query('tab', 'pending');
    $professional = null;
    $user = $request->user();

    if ($user) {
      $professional = Professional::where('user_id', $user->id)->first();
    }

    if (! $professional) {
      $professional = Professional::first();
    }

    if (! $professional) {
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
    $pendingCount = Booking::where('bookable_type', Professional::class)
      ->where('bookable_id', $professional->id)
      ->where('status_code', 'pending')
      ->count();

    $confirmedCount = Booking::where('bookable_type', Professional::class)
      ->where('bookable_id', $professional->id)
      ->where('status_code', 'confirmed')
      ->count();

    $previousCount = Booking::where('bookable_type', Professional::class)
      ->where('bookable_id', $professional->id)
      ->whereIn('status_code', ['completed', 'cancelled', 'no_show'])
      ->count();

    $patientsCount = Booking::where('bookable_type', Professional::class)
      ->where('bookable_id', $professional->id)
      ->whereNotNull('patient_id')
      ->distinct()
      ->count('patient_id');

    // Query appointments for current tab
    $query = Booking::with(['service.serviceCatalog'])
      ->where('bookable_type', Professional::class)
      ->where('bookable_id', $professional->id);

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
        'visit_type' => $booking->service?->serviceCatalog?->ar ?? $booking->service?->serviceCatalog?->en ?? 'استشارة',
        'status' => $statusLabel,
        'status_key' => $booking->status_code,
        'proposed_date' => $booking->proposed_date ? Carbon::parse($booking->proposed_date)->format('Y-m-d') : null,
        'proposed_time' => $booking->proposed_time ? Carbon::parse($booking->proposed_time)->format('H:i') : null,
        'has_pending_proposal' => (bool) $booking->has_pending_proposal,
        'can_confirm' => ($booking->status_code === 'pending' && ! $booking->has_pending_proposal),
        'can_reject' => ($booking->status_code === 'pending'),
        'can_suggest_new_time' => ($booking->status_code === 'pending' && ! $booking->has_pending_proposal),
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
   * Update booking status (confirm or reject).
   */
  public function update(Request $request, int $id): JsonResponse
  {
    $validated = $request->validate([
      'status' => ['required', 'string', 'in:confirmed,rejected'],
    ]);

    $user = $request->user();
    $professional = null;

    if ($user) {
      $professional = Professional::where('user_id', $user->id)->first();
    }

    $booking = Booking::where('id', $id);
    if ($professional) {
      $booking->where('bookable_type', Professional::class)
        ->where('bookable_id', $professional->id);
    }
    $booking = $booking->first();

    if (! $booking) {
      return response()->json(['error' => 'Booking not found'], 404);
    }

    $statusCode = $validated['status'] === 'confirmed' ? 'confirmed' : 'cancelled';

    $booking->status_code = $statusCode;
    $booking->has_pending_proposal = false; // Reset proposal since status changed
    $booking->save();

    BookingHistory::create([
      'booking_id' => $booking->id,
      'status_code' => $statusCode,
      'notes' => 'Status updated by professional',
      'changed_by' => $user?->id,
    ]);

    return response()->json([
      'success' => true,
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
    ]);

    $user = $request->user();
    $professional = null;

    if ($user) {
      $professional = Professional::where('user_id', $user->id)->first();
    }

    $booking = Booking::where('id', $id);
    if ($professional) {
      $booking->where('bookable_type', Professional::class)
        ->where('bookable_id', $professional->id);
    }
    $booking = $booking->first();

    if (! $booking) {
      return response()->json(['error' => 'Booking not found'], 404);
    }

    $booking->proposed_date = $validated['proposed_date'];
    $booking->proposed_time = $validated['proposed_time'];
    $booking->has_pending_proposal = true;
    $booking->save();

    BookingHistory::create([
      'booking_id' => $booking->id,
      'status_code' => $booking->status_code,
      'notes' => 'Reschedule suggested by professional: ' . $validated['proposed_date'] . ' ' . $validated['proposed_time'],
      'changed_by' => $user?->id,
    ]);

    return response()->json([
      'success' => true,
      'booking' => $booking,
    ]);
  }
}
