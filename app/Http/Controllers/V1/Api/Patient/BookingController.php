<?php

namespace App\Http\Controllers\V1\Api\Patient;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Center;
use App\Models\CenterService;
use App\Models\CenterWorkingHour;
use App\Models\Patient;
use App\Models\Professional;
use App\Models\ProfessionalSchedule;
use App\Models\ProfessionalService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    /**
     * Display a paginated list of the patient's bookings.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $patientId = $user && $user->patient ? $user->patient->id : null;

        if (! $patientId) {
            $firstPatient = Patient::first();
            $patientId = $firstPatient ? $firstPatient->id : null;
        }

        $page = max(1, (int) $request->query('page', 1));
        $perPage = max(1, min(100, (int) $request->query('per_page', 10)));

        $query = Booking::with([
            'bookable' => function (MorphTo $morphTo) {
                $morphTo->morphWith([
                    Professional::class => ['user', 'speciality', 'profession'],
                    Center::class => ['user', 'catalog'],
                ]);
            },
            'service.serviceCatalog',
            'status',
        ])
            ->where('patient_id', $patientId);

        if ($request->has('status_code')) {
            $query->where('status_code', $request->query('status_code'));
        }

        $paginator = $query->orderBy('booking_date', 'desc')
            ->orderBy('booking_time', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        $bookings = collect($paginator->items())->map(fn ($b) => $b->formatForPatient(false));

        return response()->json([
            'bookings' => $bookings,
            'current_page' => $paginator->currentPage(),
            'next_page' => $paginator->hasMorePages() ? $paginator->currentPage() + 1 : null,
            'total' => $paginator->total(),
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
            'bookable' => function (MorphTo $morphTo) {
                $morphTo->morphWith([
                    Professional::class => ['user', 'speciality', 'profession'],
                    Center::class => ['user', 'catalog'],
                ]);
            },
            'service.serviceCatalog',
            'schedule',
            'status',
            'bookingHistories.changedBy',
        ])->find($id);

        if (! $booking || ($patientId && $booking->patient_id !== $patientId)) {
            return response()->json([
                'message' => 'Booking not found.',
            ], 404);
        }

        return response()->json([
            'booking' => $booking->formatForPatient(true),
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
            if (! $patient) {
                $patient = Patient::create(['user_id' => $user->id]);
            }
            $patientId = $patient->id;
        } else {
            $firstPatient = Patient::first();
            if ($firstPatient) {
                $patientId = $firstPatient->id;
            }
        }

        $reference = ($bookableType === 'professional' ? 'PR-' : 'CT-').strtoupper(Str::random(8));

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

        $booking->load([
            'bookable' => function (MorphTo $morphTo) {
                $morphTo->morphWith([
                    Professional::class => ['user', 'speciality', 'profession'],
                    Center::class => ['user', 'catalog'],
                ]);
            },
            'service.serviceCatalog',
            'status',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Appointment booked successfully.',
            'booking' => $booking->formatForPatient(true),
        ], 201);
    }
}
