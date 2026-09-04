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
            CatalogSeeder::class,      // 0001_catalog_tables
            CommuneSeeder::class,      // communes from communes.json
            UserSeeder::class,         // 0002_user_tables
            PatientSeeder::class,      // 0003_patient_tables
            PartnerSeeder::class,      // 0005_partner_tables
            BookingSeeder::class,      // 0006_booking_tables
            NotificationSeeder::class, // 0007_notification_tables
            PerformanceSeeder::class,  // 0008_performance_tables
        ]);
    }
}
