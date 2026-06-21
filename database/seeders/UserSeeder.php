<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserRole;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Create user roles first
        $roles = [
            ['code' => 'admin', 'en' => 'Administrator', 'fr' => 'Administrateur', 'ar' => 'مسؤول'],
            ['code' => 'doctor', 'en' => 'Doctor', 'fr' => 'Médecin', 'ar' => 'طبيب'],
            ['code' => 'center', 'en' => 'Center', 'fr' => 'Centre', 'ar' => 'مركز'],
            ['code' => 'patient', 'en' => 'Patient', 'fr' => 'Patient', 'ar' => 'مريض'],
        ];
        foreach ($roles as $data) {
            UserRole::firstOrCreate(['code' => $data['code']], $data);
        }

        $password = Hash::make('password');

        // Admin user
        User::firstOrCreate(
            ['email' => 'admin@rendee.dz'],
            [
                'user_role_code' => 'admin',
                'name' => 'Admin',
                'full_name' => 'Admin User',
                'email_verified_at' => now(),
                'password' => $password,
                'phone_number' => '+213555000000',
                'profile_complete' => true,
            ]
        );

        // Doctor users
        $doctorNames = [
            ['name' => 'Dr. Karim', 'email' => 'karim@rendee.dz', 'full_name' => 'Dr. Karim Benali'],
            ['name' => 'Dr. Amina', 'email' => 'amina@rendee.dz', 'full_name' => 'Dr. Amina Ouali'],
            ['name' => 'Dr. Reda', 'email' => 'reda@rendee.dz', 'full_name' => 'Dr. Reda Mansouri'],
            ['name' => 'Dr. Lina', 'email' => 'lina@rendee.dz', 'full_name' => 'Dr. Lina Bouzid'],
        ];
        foreach ($doctorNames as $data) {
            User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'user_role_code' => 'doctor',
                    'name' => $data['name'],
                    'full_name' => $data['full_name'],
                    'email_verified_at' => now(),
                    'password' => $password,
                    'phone_number' => fake()->unique()->phoneNumber(),
                    'profile_complete' => true,
                ]
            );
        }

        // Center users
        $centerNames = [
            ['name' => 'Clinique El Azhar', 'email' => 'elazhar@rendee.dz', 'full_name' => 'Clinique El Azhar'],
            ['name' => 'Hopital Ibn Sina', 'email' => 'ibsina@rendee.dz', 'full_name' => 'Hopital Ibn Sina'],
            ['name' => 'Centre Nessma', 'email' => 'nessma@rendee.dz', 'full_name' => 'Centre Nessma'],
        ];
        foreach ($centerNames as $data) {
            User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'user_role_code' => 'center',
                    'name' => $data['name'],
                    'full_name' => $data['full_name'],
                    'email_verified_at' => now(),
                    'password' => $password,
                    'phone_number' => fake()->unique()->phoneNumber(),
                    'profile_complete' => true,
                ]
            );
        }

        // Patient users
        $patientNames = [
            ['name' => 'Ahmed', 'email' => 'ahmed@rendee.dz', 'full_name' => 'Ahmed Khelifi'],
            ['name' => 'Fatima', 'email' => 'fatima@rendee.dz', 'full_name' => 'Fatima Zidane'],
            ['name' => 'Rachid', 'email' => 'rachid@rendee.dz', 'full_name' => 'Rachid Belaid'],
            ['name' => 'Nadia', 'email' => 'nadia@rendee.dz', 'full_name' => 'Nadia Saidi'],
            ['name' => 'Samir', 'email' => 'samir@rendee.dz', 'full_name' => 'Samir Hocine'],
        ];
        foreach ($patientNames as $data) {
            User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'user_role_code' => 'patient',
                    'name' => $data['name'],
                    'full_name' => $data['full_name'],
                    'email_verified_at' => now(),
                    'password' => $password,
                    'phone_number' => fake()->unique()->phoneNumber(),
                    'profile_complete' => true,
                ]
            );
        }
    }
}
