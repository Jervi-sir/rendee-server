<?php

namespace Database\Seeders;

use App\Models\CenterCatalog;
use App\Models\Partner;
use App\Models\PartnerSchedule;
use App\Models\PartnerService;
use App\Models\ProfessionalSpeciality;
use App\Models\ServiceCatalog;
use App\Models\User;
use App\Models\UserContact;
use App\Models\Wilaya;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PartnerSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $partnerUsers = User::whereIn('user_role_code', ['partner', 'professional', 'center', 'pharmacist'])->get();

        foreach ($partnerUsers as $user) {
            $wilaya = Wilaya::inRandomOrder()->first();

            $speciality = null;
            $centerCatalog = null;
            $professionCode = null;

            if ($user->email && str_contains($user->email, 'center')) {
                $partnerTypeCode = 'center';
                $centerCatalog = CenterCatalog::inRandomOrder()->first();
            } elseif ($user->email && str_contains($user->email, 'pharm')) {
                $partnerTypeCode = 'pharmacist';
            } else {
                $professionCode = fake()->randomElement(['doctor', 'psychologist', 'dentist']);
                $partnerTypeCode = match ($professionCode) {
                    'doctor' => 'doctor',
                    'dentist' => 'dentist',
                    'psychologist' => 'psy',
                    default => 'professional',
                };
                $speciality = ProfessionalSpeciality::inRandomOrder()->first();
            }

            $partnerType = match ($partnerTypeCode) {
                'center' => 'CENTER',
                'pharmacist' => 'PHARM',
                default => 'PRO',
            };

            $partner = Partner::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'partner_type_code' => $partnerTypeCode,
                    'name' => $partnerTypeCode === 'center' ? 'Clinique ' . fake()->company() : ($partnerTypeCode === 'pharmacist' ? 'Pharmacie ' . fake()->firstName() : $user->full_name),
                    'profession_code' => $professionCode,
                    'professional_speciality_code' => $speciality?->code,
                    'center_catalog_code' => $centerCatalog?->code,
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
                    'emergency_24_7' => fake()->boolean(20),
                    'is_on_duty' => fake()->boolean(15),
                    'is_active' => true,
                ]
            );

            // Add Services
            $serviceCatalogs = ServiceCatalog::inRandomOrder()
                ->take(fake()->numberBetween(1, 4))
                ->get();

            foreach ($serviceCatalogs as $service) {
                PartnerService::firstOrCreate(
                    [
                        'partner_id' => $partner->id,
                        'service_catalog_code' => $service->code,
                    ],
                    [
                        'name' => $service->ar ?? $service->en,
                        'description' => fake()->sentence(),
                        'price' => fake()->randomFloat(2, 1000, 5000),
                        'duration_minutes' => fake()->randomElement([15, 30, 45, 60]),
                        'is_active' => true,
                    ]
                );
            }

            // Add Schedules
            $days = fake()->randomElements([0, 1, 2, 3, 4, 5, 6], fake()->numberBetween(5, 6));
            foreach ($days as $day) {
                PartnerSchedule::firstOrCreate(
                    [
                        'partner_id' => $partner->id,
                        'day_of_week' => $day,
                    ],
                    [
                        'start_time' => '08:00:00',
                        'end_time' => '17:00:00',
                        'is_active' => true,
                    ]
                );
            }

            // Add Contacts
            UserContact::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'platform_code' => 'phone',
                ],
                [
                    'target_user_type' => 'partner',
                    'url' => fake()->phoneNumber(),
                ]
            );

            UserContact::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'platform_code' => 'whatsapp',
                ],
                [
                    'target_user_type' => 'partner',
                    'url' => 'https://wa.me/' . fake()->numerify('2135########'),
                ]
            );
        }
    }
}
