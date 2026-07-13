<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Rating;
use App\Models\RecentSearch;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PerformanceSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Recent Searches
        $users = User::all();
        foreach ($users as $user) {
            foreach (range(1, fake()->numberBetween(1, 5)) as $i) {
                RecentSearch::create([
                    'user_id' => $user->id,
                    'label' => fake()->randomElement([
                        'General Practitioner',
                        'Cardiologist',
                        'Dentist',
                        'Physiotherapy',
                        'Vaccination',
                        'Medical Checkup',
                    ]),
                    'city' => fake()->randomElement(['Algiers', 'Oran', 'Constantine', null, null]),
                ]);
            }
        }

        // Ratings
        $bookings = Booking::whereIn('status_code', ['completed'])->get();
        foreach ($bookings as $booking) {
            if (!$booking->patient_id) {
                continue;
            }
            Rating::firstOrCreate(
                ['booking_id' => $booking->id],
                [
                    'patient_id' => $booking->patient_id,
                    'rating' => fake()->numberBetween(1, 5),
                    'review' => fake()->optional(0.7)->sentence(10),
                    'reviewable_type' => $booking->bookable_type,
                    'reviewable_id' => $booking->bookable_id,
                ]
            );
        }
    }
}
