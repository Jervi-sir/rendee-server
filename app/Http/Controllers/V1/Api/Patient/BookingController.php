<?php

namespace App\Http\Controllers\V1\Api\Patient;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Partner;
use App\Models\PartnerSchedule;
use App\Models\PartnerService;
use App\Models\Patient;
use App\Models\User;
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
            'partner' => ['user', 'speciality', 'profession', 'catalog', 'wilaya'],
            'service.catalog',
            'status',
        ])
            ->where('patient_id', $patientId);

        if ($request->has('status_code')) {
            $query->where('status_code', $request->query('status_code'));
        }

        $paginator = $query->orderBy('updated_at', 'desc')
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
            'partner' => ['user', 'speciality', 'profession', 'catalog', 'wilaya'],
            'service.catalog',
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
            'partner_id' => ['required', 'integer'],
            'date' => ['required', 'date_format:Y-m-d'],
            'time' => ['required', 'string'],
            'service_id' => ['nullable', 'integer'],
            'patient_name' => ['required', 'string', 'max:255'],
            'patient_phone' => ['required', 'string', 'max:30'],
            'notes' => ['nullable', 'string'],
        ]);

        $partnerId = $validated['partner_id'];
        $serviceId = $validated['service_id'] ?? null;
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

        $partner = Partner::find($partnerId);

        $reference = 'BK-'.strtoupper(Str::random(8));

        $bookingData = [
            'reference' => $reference,
            'patient_id' => $patientId,
            'partner_id' => $partnerId,
            'booking_date' => $date,
            'booking_time' => $validated['time'],
            'patient_name' => $validated['patient_name'],
            'patient_phone' => $validated['patient_phone'],
            'status_code' => 'pending',
            'is_center' => $partner ? ($partner->partner_type_code === 'center') : false,
            'has_pending_proposal' => false,
            'notes' => $validated['notes'] ?? null,
        ];

        if ($serviceId) {
            $bookingData['service_type'] = PartnerService::class;
            $bookingData['service_id'] = $serviceId;
        }

        $carbonDate = Carbon::parse($date);
        $schedule = PartnerSchedule::where('partner_id', $partnerId)
            ->where('day_of_week', $carbonDate->dayOfWeek)
            ->first();

        if ($schedule) {
            $bookingData['schedule_type'] = PartnerSchedule::class;
            $bookingData['schedule_id'] = $schedule->id;
        }

        $booking = Booking::create($bookingData);

        $booking->load([
            'partner' => ['user', 'speciality', 'profession', 'catalog', 'wilaya'],
            'service.catalog',
            'status',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Appointment booked successfully.',
            'booking' => $booking->formatForPatient(true),
        ], 201);
    }

    /**
     * Retrieve options for attempting a booking: prefilled patient info, partner details, services, and schedules.
     */
    public function attemptBooking(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            $user = User::first();
        }

        $partnerId = $request->query('partner_id') ?? $request->query('bookable_id');
        $partner = null;

        if ($partnerId) {
            $partner = Partner::with(['user', 'services', 'schedules'])->find($partnerId);
        }

        if (! $partner) {
            $partner = Partner::with(['user', 'services', 'schedules'])->first();
        }

        // Prefilled patient data from logged in user
        $patientInfo = [
            'full_name' => $user?->full_name ?? $user?->name ?? '',
            'first_name' => $user?->full_name ? Str::before($user->full_name, ' ') : ($user?->name ?? ''),
            'last_name' => $user?->full_name ? Str::after($user->full_name, ' ') : '',
            'email' => $user?->email ?? '',
            'phone' => $user?->phone_number ?? '',
            'phone_number' => $user?->phone_number ?? '',
        ];

        // Partner details & services
        $services = [];
        if ($partner && $partner->services) {
            $services = $partner->services->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'price' => $s->price,
                'duration_minutes' => $s->duration_minutes,
                'description' => $s->description,
                'is_active' => $s->is_active,
            ])->toArray();
        }

        // Partner Schedules
        $daysOfWeek = [
            0 => ['en' => 'Sunday', 'ar' => 'الأحد', 'fr' => 'Dimanche'],
            1 => ['en' => 'Monday', 'ar' => 'الاثنين', 'fr' => 'Lundi'],
            2 => ['en' => 'Tuesday', 'ar' => 'الثلاثاء', 'fr' => 'Mardi'],
            3 => ['en' => 'Wednesday', 'ar' => 'الأربعاء', 'fr' => 'Mercredi'],
            4 => ['en' => 'Thursday', 'ar' => 'الخميس', 'fr' => 'Jeudi'],
            5 => ['en' => 'Friday', 'ar' => 'الجمعة', 'fr' => 'Vendresse'],
            6 => ['en' => 'Saturday', 'ar' => 'السبت', 'fr' => 'Samedi'],
        ];

        $schedules = [];
        if ($partner && $partner->schedules) {
            $schedules = $partner->schedules->map(function ($sch) use ($daysOfWeek) {
                $dayMeta = $daysOfWeek[$sch->day_of_week] ?? ['en' => 'Day ' . $sch->day_of_week, 'ar' => 'اليوم ' . $sch->day_of_week, 'fr' => 'Jour ' . $sch->day_of_week];
                return [
                    'id' => $sch->id,
                    'day_of_week' => $sch->day_of_week,
                    'day_name' => $dayMeta['ar'],
                    'day_name_ar' => $dayMeta['ar'],
                    'day_name_fr' => $dayMeta['fr'],
                    'day_name_en' => $dayMeta['en'],
                    'start_time' => $sch->start_time,
                    'end_time' => $sch->end_time,
                    'slot_duration_minutes' => 30,
                    'is_active' => $sch->is_active,
                ];
            })->toArray();
        }

        $bookable = [
            'id' => $partner?->id ?? 1,
            'name' => $partner?->name ?? $partner?->user?->full_name ?? 'العيادة الطبية',
            'partner_type' => $partner?->partner_type_code ?? 'doctor',
            'is_center' => $partner ? ($partner->partner_type === 'center') : false,
        ];

        return response()->json([
            'success' => true,
            'patient_info' => $patientInfo,
            'bookable' => $bookable,
            'services' => $services,
            'schedules' => $schedules,
        ]);
    }

    /**
     * Patient accepts/confirms a proposed booking schedule change by the partner.
     */
    public function confirmProposal(Request $request, int $id): JsonResponse
    {
        $booking = Booking::find($id);

        if (! $booking) {
            return response()->json(['message' => 'Booking not found.'], 404);
        }

        if ($booking->proposed_date) {
            $booking->booking_date = $booking->proposed_date;
        }

        if ($booking->proposed_time) {
            $booking->booking_time = $booking->proposed_time;
        }

        $booking->status_code = 'confirmed';
        $booking->has_pending_proposal = false;
        $booking->proposed_date = null;
        $booking->proposed_time = null;
        $booking->save();

        $booking->load(['partner.user', 'service', 'status']);

        return response()->json([
            'success' => true,
            'message' => 'Proposal confirmed successfully.',
            'booking' => $booking->formatForPatient(true),
        ]);
    }
}
