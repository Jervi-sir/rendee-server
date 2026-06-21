<?php

namespace Database\Seeders;

use App\Models\Pharmacist;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PharmacistSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $pharmacies = [
            [
                'name' => 'صيدلية الشفاء',
                'phone' => '041-35-12-89',
                'address' => 'شارع الأمير عبد القادر، بير الجير',
                'city' => 'Oran',
                'latitude' => 35.69850000,
                'longitude' => -0.63150000,
                'is_active' => true,
            ],
            [
                'name' => 'صيدلية الهلال',
                'phone' => '041-35-77-66',
                'address' => 'نهج العقيد لطفي، وهران',
                'city' => 'Oran',
                'latitude' => 35.70220000,
                'longitude' => -0.62540000,
                'is_active' => true,
            ],
            [
                'name' => 'صيدلية ابن سينا',
                'phone' => '021-44-55-66',
                'address' => 'شارع ديدوش مراد، الجزائر الوسطى',
                'city' => 'Algiers',
                'latitude' => 36.76810000,
                'longitude' => 3.05200000,
                'is_active' => true,
            ],
            [
                'name' => 'صيدلية الصنوبر',
                'phone' => '021-21-99-00',
                'address' => 'حي الصنوبر البحري، المحمدية',
                'city' => 'Algiers',
                'latitude' => 36.73240000,
                'longitude' => 3.15120000,
                'is_active' => true,
            ],
            [
                'name' => 'صيدلية الصحة والجمال',
                'phone' => '031-92-33-44',
                'address' => 'حي المنصورة، قسنطينة',
                'city' => 'Constantine',
                'latitude' => 36.36500000,
                'longitude' => 6.61470000,
                'is_active' => true,
            ],
            [
                'name' => 'صيدلية المستقبل',
                'phone' => '038-86-77-88',
                'address' => 'وسط المدينة، عنابة',
                'city' => 'Annaba',
                'latitude' => 36.90000000,
                'longitude' => 7.76670000,
                'is_active' => true,
            ],
            [
                'name' => 'صيدلية ابن رشد',
                'phone' => '036-91-22-33',
                'address' => 'حي 1000 مسكن، سطيف',
                'city' => 'Setif',
                'latitude' => 36.19000000,
                'longitude' => 5.41390000,
                'is_active' => true,
            ],
            [
                'name' => 'صيدلية التضامن',
                'phone' => '043-27-88-99',
                'address' => 'نهج أول نوفمبر، تلمسان',
                'city' => 'Tlemcen',
                'latitude' => 34.87830000,
                'longitude' => -1.31670000,
                'is_active' => true,
            ],
        ];

        foreach ($pharmacies as $pharmacy) {
            Pharmacist::firstOrCreate(
                ['name' => $pharmacy['name']],
                $pharmacy
            );
        }
    }
}
