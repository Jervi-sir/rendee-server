<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Partner;
use App\Models\ProfessionalSpeciality;
use App\Models\Rating;
use App\Models\RecentSearch;
use App\Models\ServiceCatalog;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PerformanceSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Recent Searches
        $users = User::query()->inRandomOrder()->get();
        $specialities = ProfessionalSpeciality::query()->inRandomOrder()->get();
        $services = ServiceCatalog::query()->inRandomOrder()->get();

        foreach ($users as $user) {
            foreach (range(1, fake()->numberBetween(1, 5)) as $i) {
                RecentSearch::query()->create([
                    'user_id' => $user->id,
                    'label' => fake()->randomElement([
                        $specialities->isNotEmpty() ? $specialities->random()->en : 'General Practitioner',
                        $services->isNotEmpty() ? $services->random()->en : 'Medical Checkup',
                        'Dentist',
                        'Physiotherapy',
                        'Vaccination',
                    ]),
                    'city' => fake()->randomElement(['Algiers', 'Oran', 'Constantine', null, null]),
                ]);
            }
        }

        // Ratings
        $bookings = Booking::query()->where('status_code', 'completed')->inRandomOrder()->get();
        foreach ($bookings as $booking) {
            if (! $booking->patient_id) {
                continue;
            }
            Rating::query()->firstOrCreate(
                ['booking_id' => $booking->id],
                [
                    'patient_id' => $booking->patient_id,
                    'rating' => fake()->numberBetween(1, 5),
                    'review' => fake()->optional(0.7)->sentence(10),
                    'reviewable_type' => Partner::class,
                    'reviewable_id' => $booking->partner_id,
                ]
            );
        }
    }
}
