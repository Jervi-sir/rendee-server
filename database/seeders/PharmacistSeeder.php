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
        $users = User::where('user_role_code', 'pharmacist')->inRandomOrder()->get();

        if ($users->isEmpty()) {
            return;
        }

        foreach ($users as $user) {
            $wilaya = Wilaya::inRandomOrder()->first();

            Pharmacist::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'wilaya_code' => $wilaya?->code,
                    'name' => $user->full_name ?? $user->name,
                    'phone_public' => $user->phone_number ?? fake()->phoneNumber(),
                    'bio' => fake()->paragraph(),
                    'address' => fake()->address(),
                    'city' => fake()->randomElement(['Algiers', 'Oran', 'Bejaia', 'Constantine', 'Setif']),
                    'latitude' => 36.75 + fake()->randomFloat(6, -0.05, 0.05),
                    'longitude' => 3.05 + fake()->randomFloat(6, -0.05, 0.05),
                    'is_available' => true,
                ]
            );
        }
    }
}
