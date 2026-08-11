<?php

namespace Database\Seeders;

use App\Models\Professional;
use App\Models\ProfessionalSchedule;
use App\Models\ProfessionalService;
use App\Models\ProfessionalSpeciality;
use App\Models\ServiceCatalog;
use App\Models\User;
use App\Models\UserContact;
use App\Models\Wilaya;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProfessionalSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $users = User::where('user_role_code', 'professional')->get();

        foreach ($users as $user) {
            $speciality = ProfessionalSpeciality::inRandomOrder()->first();
            $wilaya = Wilaya::inRandomOrder()->first();

            $professional = Professional::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'profession_code' => fake()->randomElement(['doctor', 'psychologist', 'dentist']),
                    'professional_speciality_code' => $speciality?->code,
                    'wilaya_code' => $wilaya?->code,
                    'license_number' => fake()->unique()->numerify('LIC-####-####'),
                    'years_experience' => (string) fake()->numberBetween(2, 25),
                    'phone_public' => fake()->phoneNumber(),
                    'bio' => fake()->paragraph(2),
                    'address' => fake()->address(),
                    'city' => fake()->randomElement(['Algiers', 'Oran', 'Constantine', 'Annaba', 'Setif', 'Bejaia', 'Tlemcen']),
                    'latitude' => 36.75 + fake()->randomFloat(6, -0.05, 0.05),
                    'longitude' => 3.05 + fake()->randomFloat(6, -0.05, 0.05),
                    'is_available' => fake()->boolean(85),
                ]
            );

            // Add services
            $serviceCatalogs = ServiceCatalog::where('source', 'professional')
                ->orWhereNull('source')
                ->inRandomOrder()
                ->take(fake()->numberBetween(1, 3))
                ->get();

            foreach ($serviceCatalogs as $service) {
                ProfessionalService::firstOrCreate(
                    [
                        'professional_id' => $professional->id,
                        'service_catalog_code' => $service->code,
                    ],
                    [
                        'price' => fake()->randomFloat(2, 1000, 4000),
                        'duration_minutes' => fake()->randomElement([15, 30, 45, 60]),
                    ]
                );
            }

            // Add schedule slots
            $days = fake()->randomElements([0, 1, 2, 3, 4, 5, 6], fake()->numberBetween(5, 6));
            foreach ($days as $day) {
                ProfessionalSchedule::firstOrCreate(
                    [
                        'professional_id' => $professional->id,
                        'day_of_week' => $day,
                    ],
                    [
                        'start_time' => '09:00:00',
                        'end_time' => '17:00:00',
                        'is_active' => true,
                    ]
                );
            }

            // Add contacts
            $contactTypes = fake()->randomElements(['phone', 'whatsapp', 'facebook'], fake()->numberBetween(1, 2));
            foreach ($contactTypes as $platformCode) {
                UserContact::firstOrCreate(
                    [
                        'user_id' => $professional->user->id,
                        'platform_code' => $platformCode,
                    ],
                    [
                        'url' => match ($platformCode) {
                            'phone' => fake()->phoneNumber(),
                            'whatsapp' => 'https://wa.me/'.fake()->numerify('2135########'),
                            'facebook' => 'https://facebook.com/'.fake()->userName(),
                            default => fake()->url(),
                        },
                        'target_user_type' => 'professional',
                    ]
                );
            }
        }
    }
}
