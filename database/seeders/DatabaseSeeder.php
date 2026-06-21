<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            CatalogSeeder::class,
            UserSeeder::class,
            PatientSeeder::class,
            DoctorSeeder::class,
            CenterSeeder::class,
            BookingSeeder::class,
            NotificationSeeder::class,
            PerformanceSeeder::class,
            PharmacistSeeder::class,
        ]);
    }
}
