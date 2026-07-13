<?php

namespace Database\Seeders;

use App\Models\Pharmacist;
use App\Models\User;
use App\Models\Wilaya;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PharmacistSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $users = User::where('user_role_code', 'pharmacist')->get();

        $pharmaciesData = [
            [
                'name' => 'صيدلية الشفاء',
                'phone_public' => '041-35-12-89',
                'address' => 'شارع الأمير عبد القادر، بير الجير',
                'city' => 'Oran',
                'latitude' => 35.69850000,
                'longitude' => -0.63150000,
                'is_available' => true,
            ],
            [
                'name' => 'صيدلية الهلال',
                'phone_public' => '041-35-77-66',
                'address' => 'نهج العقيد لطفي، وهران',
                'city' => 'Oran',
                'latitude' => 35.70220000,
                'longitude' => -0.62540000,
                'is_available' => true,
            ],
        ];

        foreach ($users as $index => $user) {
            $data = $pharmaciesData[$index] ?? [
                'name' => 'صيدلية تجريبية ' . ($index + 1),
                'phone_public' => fake()->phoneNumber(),
                'address' => fake()->address(),
                'city' => fake()->randomElement(['Algiers', 'Bejaia', 'Constantine']),
                'latitude' => 36.75,
                'longitude' => 3.05,
                'is_available' => true,
            ];

            $wilaya = Wilaya::inRandomOrder()->first();

            Pharmacist::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'wilaya_code' => $wilaya?->code,
                    'name' => $data['name'],
                    'phone_public' => $data['phone_public'],
                    'bio' => fake()->paragraph(),
                    'address' => $data['address'],
                    'city' => $data['city'],
                    'latitude' => $data['latitude'],
                    'longitude' => $data['longitude'],
                    'is_available' => $data['is_available'],
                ]
            );
        }
    }
}
