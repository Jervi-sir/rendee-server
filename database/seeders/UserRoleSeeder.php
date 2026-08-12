<?php

namespace Database\Seeders;

use App\Models\UserRole;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserRoleSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $roles = [
            [
                'code' => UserRole::ADMIN,
                'en' => 'Administrator',
                'fr' => 'Administrateur',
                'ar' => 'مسؤول',
            ],
            [
                'code' => UserRole::PATIENT,
                'en' => 'Patient',
                'fr' => 'Patient',
                'ar' => 'مريض',
            ],
            [
                'code' => UserRole::PARTNER,
                'en' => 'Partner',
                'fr' => 'Partenaire',
                'ar' => 'شريك',
            ],
        ];

        foreach ($roles as $role) {
            UserRole::updateOrCreate(['code' => $role['code']], $role);
        }
    }
}
