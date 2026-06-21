<?php

namespace Database\Seeders;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PatientSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $patientUsers = User::where('user_role_code', 'patient')->get();

        foreach ($patientUsers as $user) {
            Patient::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'date_of_birth' => fake()->date('Y-m-d', '2005-01-01'),
                    'gender' => fake()->randomElement(['male', 'female']),
                    'address' => fake()->address(),
                    'city' => fake()->randomElement(['Algiers', 'Oran', 'Constantine', 'Annaba', 'Setif']),
                    'medical_notes' => fake()->optional(0.3)->sentence(),
                ]
            );
        }
    }
}
