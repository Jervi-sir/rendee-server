<?php

namespace Database\Seeders;

use App\Models\Center;
use App\Models\CenterCatalog;
use App\Models\CenterContact;
use App\Models\CenterService;
use App\Models\CenterWorkingHour;
use App\Models\ContactPlatform;
use App\Models\ServiceCatalog;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CenterSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $centerUsers = User::where('user_role_code', 'center')->get();

        foreach ($centerUsers as $user) {
            $centerCatalog = CenterCatalog::inRandomOrder()->first();

            $center = Center::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'name' => $user->full_name ?? $user->name,
                    'center_catalog_code' => $centerCatalog?->code,
                    'license_number' => fake()->unique()->numerify('CLI-####-####'),
                    'phone_public' => fake()->phoneNumber(),
                    'description' => fake()->paragraph(3),
                    'address' => fake()->address(),
                    'city' => fake()->randomElement(['Algiers', 'Oran', 'Constantine', 'Annaba', 'Setif']),
                    'latitude' => 35.69 + fake()->randomFloat(6, -0.05, 0.05),
                    'longitude' => -0.63 + fake()->randomFloat(6, -0.05, 0.05),
                    'emergency_24_7' => fake()->boolean(30),
                    'is_active' => true,
                ]
            );

            // Center services (2-5 per center)
            $serviceCatalogs = ServiceCatalog::inRandomOrder()->take(fake()->numberBetween(2, 5))->get();
            foreach ($serviceCatalogs as $service) {
                CenterService::firstOrCreate(
                    [
                        'center_id' => $center->id,
                        'service_catalog_code' => $service->code,
                    ],
                    [
                        'description' => fake()->sentence(),
                        'price' => fake()->randomFloat(2, 1500, 30000),
                        'duration_minutes' => fake()->randomElement([15, 30, 45, 60, 90]),
                        'is_active' => true,
                    ]
                );
            }

            // Center working hours (next 14 days)
            $startDate = now();
            for ($day = 0; $day < 14; $day++) {
                $date = $startDate->copy()->addDays($day);
                CenterWorkingHour::firstOrCreate(
                    [
                        'center_id' => $center->id,
                        'slot_date' => $date->format('Y-m-d'),
                    ],
                    [
                        'start_time' => '08:00:00',
                        'end_time' => '18:00:00',
                        'is_available' => $date->dayOfWeek !== 5, // Closed on Fridays
                    ]
                );
            }

            // Center contacts (2-3 per center)
            $platforms = ContactPlatform::inRandomOrder()->take(fake()->numberBetween(2, 3))->get();
            foreach ($platforms as $platform) {
                CenterContact::firstOrCreate(
                    [
                        'center_id' => $center->id,
                        'platform_code' => $platform->code,
                    ],
                    [
                        'url' => match ($platform->code) {
                            'phone' => fake()->phoneNumber(),
                            'email' => 'contact@' . fake()->domainName(),
                            'whatsapp' => 'https://wa.me/' . fake()->numerify('2135########'),
                            'facebook' => 'https://facebook.com/' . fake()->userName(),
                            'website' => fake()->url(),
                            default => fake()->url(),
                        },
                    ]
                );
            }
        }
    }
}
