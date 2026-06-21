<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\BookingHistory;
use App\Models\Center;
use App\Models\CenterService;
use App\Models\CenterWorkingHour;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\DoctorService;
use App\Models\Patient;
use App\Models\Status;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BookingSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $patients = Patient::all();
        $statuses = Status::all();

        // Doctor bookings
        $doctors = Doctor::all();
        foreach ($doctors as $doctor) {
            $doctorSchedules = DoctorSchedule::where('doctor_id', $doctor->id)->where('is_active', true)->get();
            $doctorServices = DoctorService::where('doctor_id', $doctor->id)->get();

            if ($doctorSchedules->isEmpty() || $doctorServices->isEmpty()) {
                continue;
            }

            foreach (range(1, fake()->numberBetween(1, 3)) as $i) {
                $patient = $patients->random();
                $schedule = $doctorSchedules->random();
                $service = $doctorServices->random();
                $status = $statuses->random();

                $bookingDate = now()->addDays(fake()->numberBetween(-10, 30))->format('Y-m-d');

                $booking = Booking::create([
                    'reference' => 'DR-' . strtoupper(Str::random(8)),
                    'patient_id' => $patient->id,
                    'bookable_type' => Doctor::class,
                    'bookable_id' => $doctor->id,
                    'doctor_service_id' => $service->id,
                    'doctor_schedule_id' => $schedule->id,
                    'center_service_id' => null,
                    'center_working_hour_id' => null,
                    'patient_name' => $patient->user->full_name ?? $patient->user->name,
                    'patient_phone' => $patient->user->phone_number ?? fake()->phoneNumber(),
                    'booking_date' => $bookingDate,
                    'booking_time' => $schedule->start_time,
                    'status_code' => $status->code,
                    'is_center' => false,
                    'proposed_date' => null,
                    'proposed_time' => null,
                    'has_pending_proposal' => false,
                    'notes' => fake()->optional(0.4)->sentence(),
                ]);

                BookingHistory::create([
                    'booking_id' => $booking->id,
                    'status_code' => $status->code,
                    'notes' => 'Booking created',
                    'changed_by' => User::inRandomOrder()->first()?->id,
                ]);
            }
        }

        // Center bookings
        $centers = Center::all();
        foreach ($centers as $center) {
            $centerServices = CenterService::where('center_id', $center->id)->where('is_active', true)->get();
            $centerHours = CenterWorkingHour::where('center_id', $center->id)->where('is_available', true)->get();

            if ($centerServices->isEmpty() || $centerHours->isEmpty()) {
                continue;
            }

            foreach (range(1, fake()->numberBetween(2, 5)) as $i) {
                $patient = $patients->random();
                $service = $centerServices->random();
                $hour = $centerHours->random();
                $status = $statuses->random();

                $booking = Booking::create([
                    'reference' => 'CT-' . strtoupper(Str::random(8)),
                    'patient_id' => $patient->id,
                    'bookable_type' => Center::class,
                    'bookable_id' => $center->id,
                    'doctor_service_id' => null,
                    'doctor_schedule_id' => null,
                    'center_service_id' => $service->id,
                    'center_working_hour_id' => $hour->id,
                    'patient_name' => $patient->user->full_name ?? $patient->user->name,
                    'patient_phone' => $patient->user->phone_number ?? fake()->phoneNumber(),
                    'booking_date' => $hour->slot_date,
                    'booking_time' => $hour->start_time,
                    'status_code' => $status->code,
                    'is_center' => true,
                    'proposed_date' => null,
                    'proposed_time' => null,
                    'has_pending_proposal' => false,
                    'notes' => fake()->optional(0.4)->sentence(),
                ]);

                BookingHistory::create([
                    'booking_id' => $booking->id,
                    'status_code' => $status->code,
                    'notes' => 'Booking created',
                    'changed_by' => User::inRandomOrder()->first()?->id,
                ]);
            }
        }
    }
}
