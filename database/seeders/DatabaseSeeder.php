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
            PartnerTypeSeeder::class,
            UserSeeder::class,
            PatientSeeder::class,
            PartnerSeeder::class,
            BookingSeeder::class,
            NotificationSeeder::class,
            PerformanceSeeder::class,
        ]);
    }
}
