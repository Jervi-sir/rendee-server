<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\DoctorContact;
use App\Models\DoctorSchedule;
use App\Models\DoctorService;
use App\Models\ServiceCatalog;
use App\Models\Speciality;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DoctorSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $doctorUsers = User::where('user_role_code', 'doctor')->get();

        foreach ($doctorUsers as $user) {
            $speciality = Speciality::inRandomOrder()->first();

            $doctor = Doctor::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'speciality_code' => $speciality?->code,
                    'license_number' => fake()->unique()->numerify('LIC-####-####'),
                    'years_experience' => (string) fake()->numberBetween(2, 25),
                    'phone_public' => fake()->phoneNumber(),
                    'bio' => fake()->paragraph(2),
                    'address' => fake()->address(),
                    'city' => fake()->randomElement(['Algiers', 'Oran', 'Constantine', 'Annaba', 'Setif', 'Bejaia', 'Tlemcen']),
                    'latitude' => 35.69 + fake()->randomFloat(6, -0.05, 0.05),
                    'longitude' => -0.63 + fake()->randomFloat(6, -0.05, 0.05),
                    'is_available' => fake()->boolean(80),
                ]
            );

            // Doctor services (1-3 per doctor)
            $serviceCatalogs = ServiceCatalog::inRandomOrder()->take(fake()->numberBetween(1, 3))->get();
            foreach ($serviceCatalogs as $service) {
                DoctorService::firstOrCreate(
                    [
                        'doctor_id' => $doctor->id,
                        'service_catalog_code' => $service->code,
                    ],
                    [
                        'price' => fake()->randomFloat(2, 1000, 10000),
                        'duration_minutes' => fake()->randomElement([15, 30, 45, 60]),
                    ]
                );
            }

            // Doctor schedules (5-6 days per week, Sunday-Thursday typical)
            $days = fake()->randomElements([0, 1, 2, 3, 4, 5, 6], fake()->numberBetween(5, 6));
            foreach ($days as $day) {
                DoctorSchedule::firstOrCreate(
                    [
                        'doctor_id' => $doctor->id,
                        'day_of_week' => $day,
                    ],
                    [
                        'start_time' => '09:00:00',
                        'end_time' => '17:00:00',
                        'is_active' => true,
                    ]
                );
            }

            // Doctor contacts (1-2 per doctor)
            $contactTypes = fake()->randomElements(['phone', 'whatsapp', 'facebook'], fake()->numberBetween(1, 2));
            foreach ($contactTypes as $platformCode) {
                DoctorContact::firstOrCreate(
                    [
                        'doctor_id' => $doctor->id,
                        'platform_code' => $platformCode,
                    ],
                    [
                        'url' => match ($platformCode) {
                            'phone' => fake()->phoneNumber(),
                            'whatsapp' => 'https://wa.me/' . fake()->numerify('2135########'),
                            'facebook' => 'https://facebook.com/' . fake()->userName(),
                            default => fake()->url(),
                        },
                    ]
                );
            }
        }
    }
}
