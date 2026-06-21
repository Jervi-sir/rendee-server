<?php

namespace App\Http\Controllers\Api\Patient\Appointment;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Center;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\CenterWorkingHour;
use App\Models\DoctorService;
use App\Models\CenterService;
use App\Models\User;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AppointmentSubmitController extends Controller
{
    public function options(Request $request, $id): JsonResponse
    {
        $bookableType = $request->query('type', 'doctor');
        $id = (int)$id;

        $bookableData = [
            'type' => $bookableType,
            'id' => $id,
            'name' => null,
            'subtitle' => '',
        ];

        $services = [];
        $slots = [];

        if ($bookableType === 'doctor') {
            $doctor = Doctor::with(['user', 'specialty', 'services.serviceCatalog'])->find($id);

            if (!$doctor) {
                return response()->json([
                    'message' => 'Doctor not found.'
                ], 404);
            }

            $bookableData['name'] = 'د. ' . ($doctor->user->full_name ?? $doctor->user->name ?? 'طبيب');
            $bookableData['subtitle'] = $doctor->specialty?->ar ?? $doctor->specialty?->en ?? 'طبيب عام';

            foreach ($doctor->services as $ds) {
                $services[] = [
                    'id' => $ds->id,
                    'name' => $ds->serviceCatalog?->ar ?? $ds->serviceCatalog?->en ?? 'استشارة',
                    'price' => $ds->price,
                ];
            }

            // Generate slots for next 7 days based on doctor's schedule
            $startDate = Carbon::today();
            for ($i = 0; $i < 7; $i++) {
                $date = $startDate->copy()->addDays($i);
                $dayOfWeek = $date->dayOfWeek; // 0 (Sunday) to 6 (Saturday)
                
                $schedule = DoctorSchedule::where('doctor_id', $doctor->id)
                    ->where('day_of_week', $dayOfWeek)
                    ->where('is_active', true)
                    ->first();

                if ($schedule) {
                    $startTime = Carbon::parse($schedule->start_time);
                    $endTime = Carbon::parse($schedule->end_time);
                    
                    while ($startTime->lt($endTime)) {
                        $timeStr = $startTime->format('H:i');
                        
                        // Check if already booked
                        $booked = Booking::where('bookable_type', Doctor::class)
                            ->where('bookable_id', $doctor->id)
                            ->where('booking_date', $date->format('Y-m-d'))
                            ->where('booking_time', $timeStr)
                            ->exists();

                        if (!$booked) {
                            $slots[] = [
                                'id' => crc32($date->format('Y-m-d') . '_' . $timeStr),
                                'date' => $date->format('Y-m-d'),
                                'date_label' => $date->translatedFormat('Y-m-d') ?? $date->format('Y-m-d'),
                                'time' => $timeStr,
                                'label' => $timeStr,
                            ];
                        }
                        $startTime->addMinutes(30);
                    }
                }
            }
        } else {
            $center = Center::with(['user', 'catalog', 'services.serviceCatalog'])->find($id);

            if (!$center) {
                return response()->json([
                    'message' => 'Center not found.'
                ], 404);
            }

            $bookableData['name'] = $center->name ?? $center->user->full_name ?? $center->user->name ?? 'مركز طبي';
            $bookableData['subtitle'] = $center->catalog?->ar ?? $center->catalog?->en ?? 'مركز طبي';

            foreach ($center->services as $cs) {
                $services[] = [
                    'id' => $cs->id,
                    'name' => $cs->serviceCatalog?->ar ?? $cs->serviceCatalog?->en ?? 'خدمة طبية',
                    'price' => $cs->price,
                ];
            }

            // Generate slots for next 7 days based on center working hours
            $startDate = Carbon::today();
            for ($i = 0; $i < 7; $i++) {
                $date = $startDate->copy()->addDays($i);
                $workingHour = CenterWorkingHour::where('center_id', $center->id)
                    ->where('slot_date', $date->format('Y-m-d'))
                    ->where('is_available', true)
                    ->first();

                if ($workingHour) {
                    foreach ($center->services as $service) {
                        $startTime = Carbon::parse($workingHour->start_time);
                        $endTime = Carbon::parse($workingHour->end_time);

                        while ($startTime->lt($endTime)) {
                            $timeStr = $startTime->format('H:i');

                            // Check if already booked for this service/center
                            $booked = Booking::where('bookable_type', Center::class)
                                ->where('bookable_id', $center->id)
                                ->where('center_service_id', $service->id)
                                ->where('booking_date', $date->format('Y-m-d'))
                                ->where('booking_time', $timeStr)
                                ->exists();

                            if (!$booked) {
                                $slots[] = [
                                    'id' => crc32($service->id . '_' . $date->format('Y-m-d') . '_' . $timeStr),
                                    'service_id' => $service->id,
                                    'service_name' => $service->serviceCatalog?->ar ?? $service->serviceCatalog?->en ?? 'خدمة',
                                    'date' => $date->format('Y-m-d'),
                                    'date_label' => $date->translatedFormat('Y-m-d') ?? $date->format('Y-m-d'),
                                    'time' => $timeStr,
                                    'label' => $timeStr,
                                ];
                            }
                            $startTime->addMinutes(30);
                        }
                    }
                }
            }
        }

        return response()->json([
            'bookable' => $bookableData,
            'services' => $services,
            'slots' => $slots,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'bookable_type' => 'required|string|in:doctor,center',
            'bookable_id' => 'required|integer',
            'date' => 'required|date_format:Y-m-d',
            'time' => 'required|string',
            'service_id' => 'nullable|integer',
            'patient_name' => 'required|string|max:255',
            'patient_phone' => 'required|string|max:30',
        ]);

        $bookableClass = $validated['bookable_type'] === 'doctor' ? Doctor::class : Center::class;
        
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

        $reference = ($validated['bookable_type'] === 'doctor' ? 'DR-' : 'CT-') . strtoupper(Str::random(8));

        $bookingData = [
            'reference' => $reference,
            'patient_id' => $patientId,
            'bookable_type' => $bookableClass,
            'bookable_id' => $validated['bookable_id'],
            'booking_date' => $validated['date'],
            'booking_time' => $validated['time'],
            'patient_name' => $validated['patient_name'],
            'patient_phone' => $validated['patient_phone'],
            'status_code' => 'pending',
            'is_center' => $validated['bookable_type'] === 'center',
            'has_pending_proposal' => false,
        ];

        if ($validated['bookable_type'] === 'center') {
            $bookingData['center_service_id'] = $validated['service_id'];
            $workingHour = CenterWorkingHour::where('center_id', $validated['bookable_id'])
                ->where('slot_date', $validated['date'])
                ->first();
            if ($workingHour) {
                $bookingData['center_working_hour_id'] = $workingHour->id;
            }
        } else {
            $bookingData['doctor_service_id'] = $validated['service_id'];
            $carbonDate = Carbon::parse($validated['date']);
            $schedule = DoctorSchedule::where('doctor_id', $validated['bookable_id'])
                ->where('day_of_week', $carbonDate->dayOfWeek)
                ->first();
            if ($schedule) {
                $bookingData['doctor_schedule_id'] = $schedule->id;
            }
        }

        $booking = Booking::create($bookingData);

        return response()->json([
            'success' => true,
            'booking' => $booking,
            'reference' => $reference,
            'id' => $booking->id,
        ], 201);
    }
}
