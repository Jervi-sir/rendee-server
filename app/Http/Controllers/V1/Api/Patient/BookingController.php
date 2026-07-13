<?php

namespace App\Http\Controllers\V1\Api\Patient;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Center;
use App\Models\Professional;
use App\Models\ProfessionalSchedule;
use App\Models\CenterWorkingHour;
use App\Models\ProfessionalService;
use App\Models\CenterService;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class BookingController extends Controller
{
    /**
     * Display a list of the patient's bookings.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $patientId = $user && $user->patient ? $user->patient->id : null;

        if (!$patientId) {
            return response()->json([
                'bookings' => [],
            ]);
        }

        $bookings = Booking::with([
            'bookable.user',
            'service',
            'status'
        ])
        ->where('patient_id', $patientId)
        ->orderBy('booking_date', 'desc')
        ->orderBy('booking_time', 'desc')
        ->get();

        return response()->json([
            'bookings' => $bookings,
        ]);
    }

    /**
     * Display detailed profile for a specific booking.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $patientId = $user && $user->patient ? $user->patient->id : null;

        $booking = Booking::with([
            'bookable.user',
            'service',
            'schedule',
            'status',
            'bookingHistories.changedBy'
        ])->find($id);

        if (!$booking || ($patientId && $booking->patient_id !== $patientId)) {
            return response()->json([
                'message' => 'Booking not found.',
            ], 404);
        }

        return response()->json([
            'booking' => $booking,
        ]);
    }

    /**
     * Create a new booking request.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'bookable_type' => ['required', 'string', 'in:professional,center'],
            'bookable_id' => ['required', 'integer'],
            'date' => ['required', 'date_format:Y-m-d'],
            'time' => ['required', 'string'],
            'service_id' => ['required', 'integer'],
            'patient_name' => ['required', 'string', 'max:255'],
            'patient_phone' => ['required', 'string', 'max:30'],
            'notes' => ['nullable', 'string'],
        ]);

        $bookableType = $validated['bookable_type'];
        $bookableId = $validated['bookable_id'];
        $serviceId = $validated['service_id'];
        $date = $validated['date'];

        $user = $request->user();
        $patientId = null;
        if ($user && $user->user_role_code === 'patient') {
            $patient = $user->patient;
            if (!$patient) {
                $patient = Patient::create(['user_id' => $user->id]);
            }
            $patientId = $patient->id;
        } else {
            $firstPatient = Patient::first();
            if ($firstPatient) {
                $patientId = $firstPatient->id;
            }
        }

        $reference = ($bookableType === 'professional' ? 'PR-' : 'CT-') . strtoupper(Str::random(8));

        $bookingData = [
            'reference' => $reference,
            'patient_id' => $patientId,
            'bookable_type' => $bookableType === 'professional' ? Professional::class : Center::class,
            'bookable_id' => $bookableId,
            'booking_date' => $date,
            'booking_time' => $validated['time'],
            'patient_name' => $validated['patient_name'],
            'patient_phone' => $validated['patient_phone'],
            'status_code' => 'pending',
            'is_center' => $bookableType === 'center',
            'has_pending_proposal' => false,
            'notes' => $validated['notes'] ?? null,
        ];

        // Resolve polymorphic service and schedule based on bookable type
        if ($bookableType === 'center') {
            $bookingData['service_type'] = CenterService::class;
            $bookingData['service_id'] = $serviceId;

            $workingHour = CenterWorkingHour::where('center_id', $bookableId)
                ->where('slot_date', $date)
                ->first();
            if ($workingHour) {
                $bookingData['schedule_type'] = CenterWorkingHour::class;
                $bookingData['schedule_id'] = $workingHour->id;
            }
        } else {
            $bookingData['service_type'] = ProfessionalService::class;
            $bookingData['service_id'] = $serviceId;

            $carbonDate = Carbon::parse($date);
            $schedule = ProfessionalSchedule::where('professional_id', $bookableId)
                ->where('day_of_week', $carbonDate->dayOfWeek)
                ->first();
            if ($schedule) {
                $bookingData['schedule_type'] = ProfessionalSchedule::class;
                $bookingData['schedule_id'] = $schedule->id;
            }
        }

        $booking = Booking::create($bookingData);

        return response()->json([
            'success' => true,
            'message' => 'Appointment booked successfully.',
            'booking' => $booking,
        ], 201);
    }
}
