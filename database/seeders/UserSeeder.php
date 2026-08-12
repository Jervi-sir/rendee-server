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
        $this->call(UserRoleSeeder::class);

        $password = Hash::make('password');

        User::query()->firstOrCreate(
            ['email' => 'admin@rendee.dz'],
            [
                'user_role_code' => UserRole::ADMIN,
                'name' => 'Admin',
                'full_name' => 'Admin User',
                'image_url' => 'https://i.pravatar.cc/300?img=1',
                'email_verified_at' => now(),
                'password' => $password,
                'password_plaintext' => 'password',
                'phone_number' => '+213555000000',
                'profile_completed' => true,
            ]
        );

        $this->createUsersForRole('pro', 24, [
            'prefix' => 'Dr.',
            'full_name_prefix' => 'Dr.',
            'email_domain' => 'rendee.dz',
        ], $password);

        $this->createUsersForRole('center', 15, [
            'prefix' => 'Center',
            'full_name_prefix' => 'Center',
            'email_domain' => 'rendee.dz',
        ], $password);

        $this->createUsersForRole('pharm', 10, [
            'prefix' => 'Pharm',
            'full_name_prefix' => 'Pharmacist',
            'email_domain' => 'rendee.dz',
        ], $password);

        $this->createUsersForRole(UserRole::PATIENT, 50, [
            'prefix' => 'Patient',
            'full_name_prefix' => null,
            'email_domain' => 'rendee.dz',
        ], $password);
    }

    private function createUsersForRole(string $typePrefix, int $count, array $options, string $password): void
    {
        $firstNames = [
            'Ahmed', 'Fatima', 'Rachid', 'Nadia', 'Samir', 'Amina', 'Karim', 'Lina', 'Yacine', 'Imane',
            'Yasmine', 'Reda', 'Sofiane', 'Meriem', 'Walid', 'Sarah', 'Hakim', 'Nour', 'Hichem', 'Aya',
        ];

        $lastNames = [
            'Benali', 'Bouzid', 'Khelifi', 'Mansouri', 'Ouali', 'Saidi', 'Meziane', 'Cherif', 'Benaissa', 'Mekki',
            'Zidane', 'Belaid', 'Haddad', 'Hamdi', 'Ferhat', 'Taleb', 'Slimani', 'Mahfouz', 'Yousef', 'Ghezal',
        ];

        $userRoleCode = $typePrefix === UserRole::PATIENT ? UserRole::PATIENT : UserRole::PARTNER;

        for ($i = 1; $i <= $count; $i++) {
            $firstName = $firstNames[($i - 1) % count($firstNames)];
            $lastName = $lastNames[($i - 1) % count($lastNames)];

            $emailName = Str::slug($firstName . $lastName) . strtolower($typePrefix) . $i;
            $email = "{$emailName}@{$options['email_domain']}";

            $fullNamePrefix = $options['full_name_prefix'] ? $options['full_name_prefix'] . ' ' : '';
            $fullName = "{$fullNamePrefix}{$firstName} {$lastName}";

            User::query()->firstOrCreate(
                ['email' => $email],
                [
                    'user_role_code' => $userRoleCode,
                    'name' => "{$options['prefix']} {$firstName}",
                    'full_name' => $fullName,
                    'image_url' => "https://i.pravatar.cc/300?u={$email}",
                    'email_verified_at' => now(),
                    'password' => $password,
                    'password_plaintext' => 'password',
                    'phone_number' => '+1' . fake()->numerify('##########'),
                    'profile_completed' => true,
                ]
            );
        }
    }
}
