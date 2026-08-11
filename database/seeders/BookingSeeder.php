<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\BookingHistory;
use App\Models\Center;
use App\Models\CenterService;
use App\Models\CenterWorkingHour;
use App\Models\Patient;
use App\Models\Professional;
use App\Models\ProfessionalSchedule;
use App\Models\ProfessionalService;
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
        $patients = Patient::inRandomOrder()->get();
        $statuses = Status::inRandomOrder()->get();

        if ($patients->isEmpty() || $statuses->isEmpty()) {
            return;
        }

        // Professional bookings
        $professionals = Professional::inRandomOrder()->get();
        foreach ($professionals as $professional) {
            $schedules = ProfessionalSchedule::where('professional_id', $professional->id)
                ->where('is_active', true)
                ->inRandomOrder()
                ->get();
            $services = ProfessionalService::where('professional_id', $professional->id)
                ->inRandomOrder()
                ->get();

            if ($schedules->isEmpty() || $services->isEmpty()) {
                continue;
            }

            foreach (range(1, fake()->numberBetween(1, 3)) as $i) {
                $patient = $patients->random();
                $schedule = $schedules->random();
                $service = $services->random();
                $status = $statuses->random();

                $booking = Booking::create([
                    'reference' => 'PR-'.strtoupper(Str::random(8)),
                    'patient_id' => $patient->id,
                    'bookable_type' => Professional::class,
                    'bookable_id' => $professional->id,
                    'service_type' => ProfessionalService::class,
                    'service_id' => $service->id,
                    'schedule_type' => ProfessionalSchedule::class,
                    'schedule_id' => $schedule->id,
                    'patient_name' => $patient->user->full_name ?? $patient->user->name,
                    'patient_phone' => $patient->user->phone_number ?? fake()->phoneNumber(),
                    'booking_date' => now()->addDays(fake()->numberBetween(-10, 30))->format('Y-m-d'),
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
        $centers = Center::inRandomOrder()->get();
        foreach ($centers as $center) {
            $services = CenterService::where('center_id', $center->id)
                ->where('is_active', true)
                ->inRandomOrder()
                ->get();
            $hours = CenterWorkingHour::where('center_id', $center->id)
                ->where('is_available', true)
                ->inRandomOrder()
                ->get();

            if ($services->isEmpty() || $hours->isEmpty()) {
                continue;
            }

            foreach (range(1, fake()->numberBetween(2, 5)) as $i) {
                $patient = $patients->random();
                $service = $services->random();
                $hour = $hours->random();
                $status = $statuses->random();

                $booking = Booking::create([
                    'reference' => 'CT-'.strtoupper(Str::random(8)),
                    'patient_id' => $patient->id,
                    'bookable_type' => Center::class,
                    'bookable_id' => $center->id,
                    'service_type' => CenterService::class,
                    'service_id' => $service->id,
                    'schedule_type' => CenterWorkingHour::class,
                    'schedule_id' => $hour->id,
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
