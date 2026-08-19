<?php

namespace Database\Seeders;

use App\Models\Patient;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PatientSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // 1. Get all patient users
        $patientUsers = User::where('user_role_code', UserRole::PATIENT)->get();

        // If no patient users, pick any random users
        if ($patientUsers->isEmpty()) {
            $patientUsers = User::inRandomOrder()->limit(5)->get();
        }

        $bloodTypes = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
        $allergiesList = ['Pénicilline', 'Pollen', 'Arachides', 'Aspirine', 'Acariens', 'Lactose', 'Gluten'];
        $chronicDiseasesList = ['Diabète Type 2', 'Hypertension artérielle', 'Asthme', 'Hypothyroïdie', 'Allergie saisonnière'];
        $cities = ['Alger', 'Oran', 'Constantine', 'Annaba', 'Blida', 'Sétif', 'Tlemcen', 'Batna', 'Chlef', 'Béjaïa'];

        foreach ($patientUsers as $user) {
            $hasAllergies = fake()->boolean(40);
            $hasChronic = fake()->boolean(30);

            Patient::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'date_of_birth' => fake()->dateTimeBetween('-65 years', '-18 years')->format('Y-m-d'),
                    'gender' => fake()->randomElement(['male', 'female']),
                    'address' => fake()->streetAddress(),
                    'city' => fake()->randomElement($cities),
                    'blood_type' => fake()->randomElement($bloodTypes),
                    'allergies' => $hasAllergies ? fake()->randomElements($allergiesList, fake()->numberBetween(1, 2)) : [],
                    'chronic_diseases' => $hasChronic ? fake()->randomElements($chronicDiseasesList, fake()->numberBetween(1, 2)) : [],
                    'medications' => $hasChronic ? ['Traitement quotidien prescrit'] : [],
                    'emergency_contacts' => [
                        [
                            'name' => fake()->name(),
                            'relationship' => fake()->randomElement(['Parent', 'Conjoint', 'Frère/Sœur', 'Ami']),
                            'phone' => '05'.fake()->numerify('########'),
                        ],
                    ],
                    'medical_notes' => fake()->boolean(30) ? 'Dossier médical standard - Pas de contre-indications majeures.' : null,
                ]
            );
        }
    }
}
