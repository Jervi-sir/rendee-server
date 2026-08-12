<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\BookingHistory;
use App\Models\Partner;
use App\Models\PartnerSchedule;
use App\Models\PartnerService;
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
        $patients = Patient::inRandomOrder()->get();
        $statuses = Status::inRandomOrder()->get();
        $partners = Partner::inRandomOrder()->get();

        if ($patients->isEmpty() || $statuses->isEmpty() || $partners->isEmpty()) {
            return;
        }

        foreach ($partners as $partner) {
            $schedules = PartnerSchedule::where('partner_id', $partner->id)
                ->where('is_active', true)
                ->inRandomOrder()
                ->get();
            $services = PartnerService::where('partner_id', $partner->id)
                ->inRandomOrder()
                ->get();

            if ($schedules->isEmpty() || $services->isEmpty()) {
                continue;
            }

            foreach (range(1, fake()->numberBetween(1, 4)) as $i) {
                $patient = $patients->random();
                $schedule = $schedules->random();
                $service = $services->random();
                $status = $statuses->random();

                $booking = Booking::create([
                    'reference' => 'BK-' . strtoupper(Str::random(8)),
                    'patient_id' => $patient->id,
                    'partner_id' => $partner->id,
                    'service_type' => PartnerService::class,
                    'service_id' => $service->id,
                    'schedule_type' => PartnerSchedule::class,
                    'schedule_id' => $schedule->id,
                    'patient_name' => $patient->user?->full_name ?? $patient->user?->name ?? 'Patient',
                    'patient_phone' => $patient->user?->phone_number ?? fake()->phoneNumber(),
                    'booking_date' => now()->addDays(fake()->numberBetween(-10, 30))->format('Y-m-d'),
                    'booking_time' => $schedule->start_time,
                    'status_code' => $status->code,
                    'is_center' => $partner->partner_type === 'CENTER',
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
