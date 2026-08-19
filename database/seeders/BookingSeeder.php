<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\BookingHistory;
use App\Models\Partner;
use App\Models\Patient;
use App\Models\Status;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BookingSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $patients = Patient::with('user')->get();
        $partners = Partner::with(['services', 'schedules'])->get();
        $statuses = Status::all();
        $allUsers = User::all();

        if ($patients->isEmpty() || $partners->isEmpty()) {
            return;
        }

        $statusList = ['pending', 'confirmed', 'completed', 'cancelled', 'rescheduled'];

        for ($i = 1; $i <= 20; $i++) {
            $patient = $patients->random();
            $partner = $partners->random();
            $statusCode = fake()->randomElement($statusList);
            $service = $partner->services->isNotEmpty() ? $partner->services->random() : null;
            $schedule = $partner->schedules->where('is_active', true)->first();

            $bookingDate = Carbon::today()->addDays(fake()->numberBetween(-14, 14))->format('Y-m-d');
            $bookingTime = fake()->randomElement(['09:00:00', '10:30:00', '11:00:00', '14:00:00', '15:30:00', '16:00:00']);

            $booking = Booking::create([
                'reference' => 'BK-'.strtoupper(Str::random(8)),
                'patient_id' => $patient->id,
                'partner_id' => $partner->id,
                'service_type' => 'partner_service',
                'service_id' => $service?->id,
                'schedule_type' => 'partner_schedule',
                'schedule_id' => $schedule?->id,
                'patient_name' => $patient->user?->name ?? 'Patient '.$i,
                'patient_phone' => $patient->user?->phone_number ?? ('05'.fake()->numerify('########')),
                'booking_date' => $bookingDate,
                'booking_time' => $bookingTime,
                'status_code' => $statusCode,
                'is_center' => $partner->partner_type_code === 'center',
                'proposed_date' => $statusCode === 'rescheduled' ? Carbon::parse($bookingDate)->addDays(2)->format('Y-m-d') : null,
                'proposed_time' => $statusCode === 'rescheduled' ? '11:00:00' : null,
                'has_pending_proposal' => $statusCode === 'rescheduled',
                'notes' => fake()->boolean(40) ? 'Consultation médicale de suivi.' : null,
            ]);

            // 2. Booking Histories (Isolated)
            // Initial creation history
            BookingHistory::create([
                'booking_id' => $booking->id,
                'status_code' => 'pending',
                'notes' => 'Rendez-vous créé par le patient.',
                'changed_by' => $patient->user_id,
            ]);

            // Current status history if progressed
            if ($statusCode !== 'pending') {
                BookingHistory::create([
                    'booking_id' => $booking->id,
                    'status_code' => $statusCode,
                    'notes' => 'Statut mis à jour: '.$statusCode,
                    'changed_by' => $allUsers->isNotEmpty() ? $allUsers->random()->id : $partner->user_id,
                ]);
            }
        }
    }
}
