<?php

namespace Database\Seeders;

use App\Models\PartnerType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PartnerTypeSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $types = [
            [
                'code' => PartnerType::DOCTOR,
                'en' => 'Doctor',
                'fr' => 'Médecin',
                'ar' => 'طبيب',
            ],
            [
                'code' => PartnerType::DENTIST,
                'en' => 'Dentist',
                'fr' => 'Dentiste',
                'ar' => 'طبيب أسنان',
            ],
            [
                'code' => PartnerType::PHARMACIST,
                'en' => 'Pharmacist',
                'fr' => 'Pharmacien',
                'ar' => 'صيدلي',
            ],
            [
                'code' => PartnerType::CENTER,
                'en' => 'Medical Center',
                'fr' => 'Centre Médical',
                'ar' => 'مركز طبي',
            ],
            [
                'code' => PartnerType::PSY,
                'en' => 'Psychologist',
                'fr' => 'Psychologue',
                'ar' => 'أخصائي نفساني',
            ],
            [
                'code' => PartnerType::PROFESSIONAL,
                'en' => 'Professional',
                'fr' => 'Professionnel',
                'ar' => 'أخصائي',
            ],
        ];

        foreach ($types as $type) {
            PartnerType::updateOrCreate(['code' => $type['code']], $type);
        }
    }
}
