<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Partner;
use App\Models\Patient;
use App\Models\Rating;
use App\Models\RecentSearch;
use App\Models\ServiceCatalog;
use App\Models\Speciality;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PerformanceSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $users = User::all();
        $specialities = Speciality::all();
        $services = ServiceCatalog::all();
        $partners = Partner::all();
        $completedBookings = Booking::where('status_code', 'completed')->get();

        if ($completedBookings->isEmpty()) {
            $completedBookings = Booking::all();
        }

        // 1. Recent Searches (Isolated)
        $searchLabels = [
            'Cardiologue',
            'Dentiste',
            'Pédiatre',
            'Gynécologue',
            'Médecin Généraliste',
            'Dermatologue',
            'Ophtalmologue',
            'Laboratoire d\'analyses',
            'Clinique privée',
            'Consultation générale',
            'Bilan de santé',
            'Scanner et IRM',
        ];

        $cities = ['Alger', 'Oran', 'Constantine', 'Annaba', 'Blida', 'Sétif', 'Tlemcen', 'Batna', 'Chlef', 'Béjaïa', null];

        foreach ($users as $user) {
            $count = fake()->numberBetween(1, 4);
            for ($i = 0; $i < $count; $i++) {
                $label = fake()->randomElement($searchLabels);
                if ($specialities->isNotEmpty() && fake()->boolean(40)) {
                    $label = $specialities->random()->fr ?? $specialities->random()->en;
                } elseif ($services->isNotEmpty() && fake()->boolean(30)) {
                    $label = $services->random()->fr ?? $services->random()->en;
                }

                RecentSearch::create([
                    'user_id' => $user->id,
                    'label' => $label,
                    'city' => fake()->randomElement($cities),
                ]);
            }
        }

        // 2. Ratings & Reviews (Isolated on completed bookings & partners)
        $reviews = [
            'Excellent médecin, très à l\'écoute et ponctuel. Je recommande vivement.',
            'Très bon accueil au cabinet, explications claires et professionnelles.',
            'Consultation rapide et efficace, prise en charge rassurante.',
            'Cabinet propre et moderne, le docteur est très compétent.',
            'Service de qualité, rendez-vous respecté à l\'heure exacte.',
            'طبيب ممتاز ومحترف جداً، بارك الله فيه.',
            'استقبال رائع وتكفل ممتاز بالمريض، أنصح به بشدة.',
            'خدمة جيدة وفحص دقيق وشامل.',
        ];

        foreach ($completedBookings as $booking) {
            if ($booking->patient_id && $booking->partner_id) {
                Rating::create([
                    'booking_id' => $booking->id,
                    'patient_id' => $booking->patient_id,
                    'rating' => fake()->randomElement([4, 5, 5, 4, 3, 5]),
                    'review' => fake()->randomElement($reviews),
                    'reviewable_type' => Partner::class,
                    'reviewable_id' => $booking->partner_id,
                ]);
            }
        }
    }
}
