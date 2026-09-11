<?php

namespace Database\Seeders;

use App\Models\ContactPlatform;
use App\Models\User;
use App\Models\UserContact;
use App\Models\UserDevice;
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
        // 1. User Roles
        $roles = [
            ['code' => UserRole::ADMIN, 'en' => 'Admin', 'fr' => 'Administrateur', 'ar' => 'مسؤول'],
            ['code' => UserRole::PATIENT, 'en' => 'Patient', 'fr' => 'Patient', 'ar' => 'مريض'],
            ['code' => UserRole::PARTNER, 'en' => 'Partner', 'fr' => 'Partenaire', 'ar' => 'شريك'],
        ];

        foreach ($roles as $role) {
            UserRole::updateOrCreate(['code' => $role['code']], $role);
        }

        // 2. Core Demo Users
        $defaultPassword = 'password123';
        $hashedPassword = Hash::make($defaultPassword);

        $coreUsers = [
            [
                'email' => 'admin@rendee.dz',
                'name' => 'Admin Rendee',
                'full_name' => 'Administrateur Rendee',
                'user_role_code' => UserRole::ADMIN,
                'phone_number' => '0550000000',
                'profile_completed' => true,
            ],
            [
                'email' => 'patient@rendee.dz',
                'name' => 'Ahmed Benali',
                'full_name' => 'Ahmed Benali',
                'user_role_code' => UserRole::PATIENT,
                'phone_number' => '0551111111',
                'profile_completed' => true,
            ],
            [
                'email' => 'patient2@rendee.dz',
                'name' => 'Fatima Zohra',
                'full_name' => 'Fatima Zohra Mansouri',
                'user_role_code' => UserRole::PATIENT,
                'phone_number' => '0551111112',
                'profile_completed' => true,
            ],
            [
                'email' => 'doctor@rendee.dz',
                'name' => 'Dr. Karim Amrani',
                'full_name' => 'Dr. Karim Amrani',
                'user_role_code' => UserRole::PARTNER,
                'phone_number' => '0552222222',
                'profile_completed' => true,
                'image_url' => 'https://images.unsplash.com/photo-1622253692010-333f2da6031d?w=300&auto=format&fit=crop&q=80',
            ],
            [
                'email' => 'dentist@rendee.dz',
                'name' => 'Dr. Yasmine Belkacem',
                'full_name' => 'Dr. Yasmine Belkacem',
                'user_role_code' => UserRole::PARTNER,
                'phone_number' => '0553333333',
                'profile_completed' => true,
                'image_url' => 'https://images.unsplash.com/photo-1594824813689-0268595dfefb?w=300&auto=format&fit=crop&q=80',
            ],
            [
                'email' => 'center@rendee.dz',
                'name' => 'Clinique El Chifa',
                'full_name' => 'Clinique Médico-Chirurgicale El Chifa',
                'user_role_code' => UserRole::PARTNER,
                'phone_number' => '0554444444',
                'profile_completed' => true,
                'image_url' => 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?w=300&auto=format&fit=crop&q=80',
            ],
            [
                'email' => 'pharmacy@rendee.dz',
                'name' => 'Pharmacie Centrale',
                'full_name' => 'Pharmacie Centrale d\'Alger',
                'user_role_code' => UserRole::PARTNER,
                'phone_number' => '0555555555',
                'profile_completed' => true,
                'image_url' => 'https://images.unsplash.com/photo-1586015555751-63c2992982d1?w=300&auto=format&fit=crop&q=80',
            ],
            [
                'email' => 'psy@rendee.dz',
                'name' => 'Dr. Samia Touati',
                'full_name' => 'Dr. Samia Touati',
                'user_role_code' => UserRole::PARTNER,
                'phone_number' => '0556666666',
                'profile_completed' => true,
                'image_url' => 'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?w=300&auto=format&fit=crop&q=80',
            ],
        ];

        foreach ($coreUsers as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                array_merge($userData, [
                    'password' => $hashedPassword,
                    'password_plaintext' => $defaultPassword,
                    'email_verified_at' => now(),
                ])
            );
        }

        // Additional Randomized Users
        $algerianFirstNames = ['Mohamed', 'Amine', 'Youcef', 'Walid', 'Sofiane', 'Nadia', 'Sarah', 'Khadidja', 'Imene', 'Meriem', 'Ryad', 'Bilel', 'Lynda', 'Soumia'];
        $algerianLastNames = ['Brahimi', 'Saadi', 'Meziane', 'Bouzid', 'Haddad', 'Cherif', 'Zitouni', 'Hamidi', 'Khelil', 'Slimani', 'Boudiaf', 'Dahmani'];
        $rolesList = [UserRole::PATIENT, UserRole::PARTNER];

        $partnerAvatars = [
            'https://images.unsplash.com/photo-1622253692010-333f2da6031d?w=300&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1594824813689-0268595dfefb?w=300&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?w=300&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1537368910025-700350fe46c7?w=300&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1582750433449-648ed127bb54?w=300&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?w=300&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1622902046580-2b47f47f5471?w=300&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1527613426441-4da17471b66d?w=300&auto=format&fit=crop&q=80',
        ];

        for ($i = 1; $i <= 15; $i++) {
            $firstName = fake()->randomElement($algerianFirstNames);
            $lastName = fake()->randomElement($algerianLastNames);
            $role = fake()->randomElement($rolesList);
            $email = strtolower($firstName.'.'.$lastName.$i.'@rendee.dz');
            $img = $role === UserRole::PARTNER ? fake()->randomElement($partnerAvatars) : null;

            User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $firstName.' '.$lastName,
                    'full_name' => ($role === UserRole::PARTNER ? 'Dr. ' : '').$firstName.' '.$lastName,
                    'user_role_code' => $role,
                    'password' => $hashedPassword,
                    'password_plaintext' => $defaultPassword,
                    'phone_number' => '05'.fake()->numerify('########'),
                    'image_url' => $img,
                    'profile_completed' => true,
                    'email_verified_at' => now(),
                ]
            );
        }

        // 3. User Contacts (Isolated & picking random ContactPlatform)
        $allUsers = User::all();
        $platforms = ContactPlatform::all();

        foreach ($allUsers as $user) {
            if ($platforms->isNotEmpty() && fake()->boolean(70)) {
                $platform = $platforms->random();
                UserContact::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'platform_code' => $platform->code,
                    ],
                    [
                        'url' => match ($platform->code) {
                            'phone', 'whatsapp', 'viber' => $user->phone_number ?? '0550000000',
                            'email' => $user->email,
                            'website' => 'https://'.Str::slug($user->name).'.dz',
                            default => 'https://'.$platform->code.'.com/'.Str::slug($user->name),
                        },
                        'target_user_type' => $user->user_role_code,
                    ]
                );
            }

            // 4. User Devices (Isolated device records)
            UserDevice::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'device_id' => 'dev_'.md5($user->email.'_primary'),
                ],
                [
                    'device_name' => fake()->randomElement(['iPhone 15 Pro', 'Samsung Galaxy S24', 'Pixel 8', 'Xiaomi 13', 'MacBook Air']),
                    'device_type' => fake()->randomElement(['ios', 'android', 'web']),
                    'device_model' => fake()->randomElement(['iPhone15,2', 'SM-S928B', 'Pixel 8', 'Web Browser']),
                    'os_version' => fake()->randomElement(['iOS 17.5', 'Android 14', 'macOS 14.5', 'Windows 11']),
                    'app_version' => '1.0.0',
                    'push_notification_token' => 'fcm_'.Str::random(32),
                    'push_notifications_enabled' => true,
                    'language' => fake()->randomElement(['ar', 'fr', 'en']),
                    'timezone' => 'Africa/Algiers',
                    'is_active' => true,
                    'last_active_at' => now(),
                    'last_logged_in_at' => now(),
                ]
            );
        }
    }
}
