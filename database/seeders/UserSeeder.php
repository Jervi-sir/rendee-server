<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserRole;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $roles = [
            ['code' => 'admin', 'en' => 'Administrator', 'fr' => 'Administrateur', 'ar' => 'مسؤول'],
            ['code' => 'patient', 'en' => 'Patient', 'fr' => 'Patient', 'ar' => 'مريض'],
            ['code' => 'professional', 'en' => 'Professional', 'fr' => 'Professionnel', 'ar' => 'أخصائي/طبيب'],
            ['code' => 'center', 'en' => 'Center', 'fr' => 'Centre', 'ar' => 'مركز'],
            ['code' => 'pharmacist', 'en' => 'Pharmacist', 'fr' => 'Pharmacien', 'ar' => 'صيدلي'],
        ];

        foreach ($roles as $data) {
            UserRole::query()->firstOrCreate(['code' => $data['code']], $data);
        }

        $password = Hash::make('password');

        User::query()->firstOrCreate(
            ['email' => 'admin@rendee.dz'],
            [
                'user_role_code' => 'admin',
                'name' => 'Admin',
                'full_name' => 'Admin User',
                'image_url' => 'https://i.pravatar.cc/300?img=1',
                'email_verified_at' => now(),
                'password' => $password,
                'password_plaintext' => 'password',
                'phone_number' => '+213555000000',
                'profile_complete' => true,
            ]
        );

        $this->createUsersForRole('professional', 24, [
            'prefix' => 'Dr.',
            'full_name_prefix' => 'Dr.',
            'email_domain' => 'rendee.dz',
        ], $password);

        $this->createUsersForRole('center', 15, [
            'prefix' => 'Center',
            'full_name_prefix' => 'Center',
            'email_domain' => 'rendee.dz',
        ], $password);

        $this->createUsersForRole('pharmacist', 10, [
            'prefix' => 'Pharm',
            'full_name_prefix' => 'Pharmacist',
            'email_domain' => 'rendee.dz',
        ], $password);

        $this->createUsersForRole('patient', 50, [
            'prefix' => 'Patient',
            'full_name_prefix' => null,
            'email_domain' => 'rendee.dz',
        ], $password);
    }

    private function createUsersForRole(string $roleCode, int $count, array $options, string $password): void
    {
        $firstNames = [
            'Ahmed',
            'Fatima',
            'Rachid',
            'Nadia',
            'Samir',
            'Amina',
            'Karim',
            'Lina',
            'Yacine',
            'Imane',
            'Yasmine',
            'Reda',
            'Sofiane',
            'Meriem',
            'Walid',
            'Sarah',
            'Hakim',
            'Nour',
            'Hichem',
            'Aya',
        ];

        $lastNames = [
            'Benali',
            'Bouzid',
            'Khelifi',
            'Mansouri',
            'Ouali',
            'Saidi',
            'Meziane',
            'Cherif',
            'Benaissa',
            'Mekki',
            'Zidane',
            'Belaid',
            'Haddad',
            'Boukerche',
            'Boudiaf',
            'Amrani',
            'Ferhat',
            'Gacem',
            'Kaci',
            'Tahar',
        ];

        for ($i = 1; $i <= $count; $i++) {
            $firstName = fake()->randomElement($firstNames);
            $lastName = fake()->randomElement($lastNames);
            $fullName = trim(($options['full_name_prefix'] ? $options['full_name_prefix'].' ' : '').$firstName.' '.$lastName);
            $emailPrefix = Str::slug($firstName.'.'.$lastName.'.'.$roleCode.'.'.$i);

            User::query()->firstOrCreate(
                ['email' => $emailPrefix.'@'.$options['email_domain']],
                [
                    'user_role_code' => $roleCode,
                    'name' => $options['prefix'].' '.$firstName,
                    'full_name' => $fullName,
                    'image_url' => 'https://i.pravatar.cc/300?u='.rawurlencode($emailPrefix.'@'.$options['email_domain']),
                    'email_verified_at' => now(),
                    'password' => $password,
                    'password_plaintext' => 'password',
                    'phone_number' => fake()->unique()->phoneNumber(),
                    'profile_complete' => true,
                ]
            );
        }
    }
}
