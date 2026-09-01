<?php

namespace Database\Seeders;

use App\Models\ContactPlatform;
use App\Models\Status;
use App\Models\Wilaya;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // 1. Wilayas (58 Algerian Wilayas)
        $wilayas = [
            ['code' => '01', 'number' => '01', 'en' => 'Adrar', 'fr' => 'Adrar', 'ar' => 'أدرار'],
            ['code' => '02', 'number' => '02', 'en' => 'Chlef', 'fr' => 'Chlef', 'ar' => 'الشلف'],
            ['code' => '03', 'number' => '03', 'en' => 'Laghouat', 'fr' => 'Laghouat', 'ar' => 'الأغواط'],
            ['code' => '04', 'number' => '04', 'en' => 'Oum El Bouaghi', 'fr' => 'Oum El Bouaghi', 'ar' => 'أم البواقي'],
            ['code' => '05', 'number' => '05', 'en' => 'Batna', 'fr' => 'Batna', 'ar' => 'باتنة'],
            ['code' => '06', 'number' => '06', 'en' => 'Béjaïa', 'fr' => 'Béjaïa', 'ar' => 'بجاية'],
            ['code' => '07', 'number' => '07', 'en' => 'Biskra', 'fr' => 'Biskra', 'ar' => 'بسكرة'],
            ['code' => '08', 'number' => '08', 'en' => 'Béchar', 'fr' => 'Béchar', 'ar' => 'بشار'],
            ['code' => '09', 'number' => '09', 'en' => 'Blida', 'fr' => 'Blida', 'ar' => 'البليدة'],
            ['code' => '10', 'number' => '10', 'en' => 'Bouira', 'fr' => 'Bouira', 'ar' => 'البويرة'],
            ['code' => '11', 'number' => '11', 'en' => 'Tamanrasset', 'fr' => 'Tamanrasset', 'ar' => 'تمنراست'],
            ['code' => '12', 'number' => '12', 'en' => 'Tébessa', 'fr' => 'Tébessa', 'ar' => 'تبسة'],
            ['code' => '13', 'number' => '13', 'en' => 'Tlemcen', 'fr' => 'Tlemcen', 'ar' => 'تلمسان'],
            ['code' => '14', 'number' => '14', 'en' => 'Tiaret', 'fr' => 'Tiaret', 'ar' => 'تيارت'],
            ['code' => '15', 'number' => '15', 'en' => 'Tizi Ouzou', 'fr' => 'Tizi Ouzou', 'ar' => 'تيزي وزو'],
            ['code' => '16', 'number' => '16', 'en' => 'Algiers', 'fr' => 'Alger', 'ar' => 'الجزائر'],
            ['code' => '17', 'number' => '17', 'en' => 'Djelfa', 'fr' => 'Djelfa', 'ar' => 'الجلفة'],
            ['code' => '18', 'number' => '18', 'en' => 'Jijel', 'fr' => 'Jijel', 'ar' => 'جيجل'],
            ['code' => '19', 'number' => '19', 'en' => 'Sétif', 'fr' => 'Sétif', 'ar' => 'سطيف'],
            ['code' => '20', 'number' => '20', 'en' => 'Saïda', 'fr' => 'Saïda', 'ar' => 'سعيدة'],
            ['code' => '21', 'number' => '21', 'en' => 'Skikda', 'fr' => 'Skikda', 'ar' => 'سكيكدة'],
            ['code' => '22', 'number' => '22', 'en' => 'Sidi Bel Abbès', 'fr' => 'Sidi Bel Abbès', 'ar' => 'سيدي بلعباس'],
            ['code' => '23', 'number' => '23', 'en' => 'Annaba', 'fr' => 'Annaba', 'ar' => 'عنابة'],
            ['code' => '24', 'number' => '24', 'en' => 'Guelma', 'fr' => 'Guelma', 'ar' => 'قالمة'],
            ['code' => '25', 'number' => '25', 'en' => 'Constantine', 'fr' => 'Constantine', 'ar' => 'قسنطينة'],
            ['code' => '26', 'number' => '26', 'en' => 'Médéa', 'fr' => 'Médéa', 'ar' => 'المدية'],
            ['code' => '27', 'number' => '27', 'en' => 'Mostaganem', 'fr' => 'Mostaganem', 'ar' => 'مستغانم'],
            ['code' => '28', 'number' => '28', 'en' => 'M\'Sila', 'fr' => 'M\'Sila', 'ar' => 'المسيلة'],
            ['code' => '29', 'number' => '29', 'en' => 'Mascara', 'fr' => 'Mascara', 'ar' => 'معسكر'],
            ['code' => '30', 'number' => '30', 'en' => 'Ouargla', 'fr' => 'Ouargla', 'ar' => 'ورقلة'],
            ['code' => '31', 'number' => '31', 'en' => 'Oran', 'fr' => 'Oran', 'ar' => 'وهران'],
            ['code' => '32', 'number' => '32', 'en' => 'El Bayadh', 'fr' => 'El Bayadh', 'ar' => 'البيض'],
            ['code' => '33', 'number' => '33', 'en' => 'Illizi', 'fr' => 'Illizi', 'ar' => 'إليزي'],
            ['code' => '34', 'number' => '34', 'en' => 'Bordj Bou Arréridj', 'fr' => 'Bordj Bou Arréridj', 'ar' => 'برج بوعريريج'],
            ['code' => '35', 'number' => '35', 'en' => 'Boumerdès', 'fr' => 'Boumerdès', 'ar' => 'بومرداس'],
            ['code' => '36', 'number' => '36', 'en' => 'El Tarf', 'fr' => 'El Tarf', 'ar' => 'الطارف'],
            ['code' => '37', 'number' => '37', 'en' => 'Tindouf', 'fr' => 'Tindouf', 'ar' => 'تندوف'],
            ['code' => '38', 'number' => '38', 'en' => 'Tissemsilt', 'fr' => 'Tissemsilt', 'ar' => 'تيسمسيلت'],
            ['code' => '39', 'number' => '39', 'en' => 'El Oued', 'fr' => 'El Oued', 'ar' => 'الوادي'],
            ['code' => '40', 'number' => '40', 'en' => 'Khenchela', 'fr' => 'Khenchela', 'ar' => 'خنشلة'],
            ['code' => '41', 'number' => '41', 'en' => 'Souk Ahras', 'fr' => 'Souk Ahras', 'ar' => 'سوق أهراس'],
            ['code' => '42', 'number' => '42', 'en' => 'Tipaza', 'fr' => 'Tipaza', 'ar' => 'تيبازة'],
            ['code' => '43', 'number' => '43', 'en' => 'Mila', 'fr' => 'Mila', 'ar' => 'ميلة'],
            ['code' => '44', 'number' => '44', 'en' => 'Aïn Defla', 'fr' => 'Aïn Defla', 'ar' => 'عين الدفلى'],
            ['code' => '45', 'number' => '45', 'en' => 'Naâma', 'fr' => 'Naâma', 'ar' => 'النعامة'],
            ['code' => '46', 'number' => '46', 'en' => 'Aïn Témouchent', 'fr' => 'Aïn Témouchent', 'ar' => 'عين تموشنت'],
            ['code' => '47', 'number' => '47', 'en' => 'Ghardaïa', 'fr' => 'Ghardaïa', 'ar' => 'غرداية'],
            ['code' => '48', 'number' => '48', 'en' => 'Relizane', 'fr' => 'Relizane', 'ar' => 'غليزان'],
            ['code' => '49', 'number' => '49', 'en' => 'Timimoun', 'fr' => 'Timimoun', 'ar' => 'تيميمون'],
            ['code' => '50', 'number' => '50', 'en' => 'Bordj Badji Mokhtar', 'fr' => 'Bordj Badji Mokhtar', 'ar' => 'برج باجي مختار'],
            ['code' => '51', 'number' => '51', 'en' => 'Ouled Djellal', 'fr' => 'Ouled Djellal', 'ar' => 'أولاد جلال'],
            ['code' => '52', 'number' => '52', 'en' => 'Béni Abbès', 'fr' => 'Béni Abbès', 'ar' => 'بني عباس'],
            ['code' => '53', 'number' => '53', 'en' => 'In Salah', 'fr' => 'In Salah', 'ar' => 'عين صالح'],
            ['code' => '54', 'number' => '54', 'en' => 'In Guezzam', 'fr' => 'In Guezzam', 'ar' => 'عين قزام'],
            ['code' => '55', 'number' => '55', 'en' => 'Touggourt', 'fr' => 'Touggourt', 'ar' => 'تقرت'],
            ['code' => '56', 'number' => '56', 'en' => 'Djanet', 'fr' => 'Djanet', 'ar' => 'جانت'],
            ['code' => '57', 'number' => '57', 'en' => 'El M\'Ghair', 'fr' => 'El M\'Ghair', 'ar' => 'المغير'],
            ['code' => '58', 'number' => '58', 'en' => 'El Meniaa', 'fr' => 'El Meniaa', 'ar' => 'المنيعة'],
        ];

        foreach ($wilayas as $data) {
            $codeKey = sprintf('%02d', (int) $data['code']);
            $coords = Wilaya::$wilayaCoordinates[$codeKey] ?? ['lat' => 35.6971, 'lng' => -0.6308];
            $data['latitude'] = $coords['lat'];
            $data['longitude'] = $coords['lng'];
            $data['lat'] = $coords['lat'];
            $data['lng'] = $coords['lng'];
            Wilaya::updateOrCreate(['code' => $data['code']], $data);
        }

        // 2. Communes (Key sample communes per wilaya)
        $communes = [
            // Algiers (16)
            ['wilaya_code' => '16', 'code' => '1601', 'postal_code' => '16000', 'en' => 'Alger Centre', 'fr' => 'Alger Centre', 'ar' => 'الجزائر الوسطى', 'lat' => 36.7753, 'lng' => 3.0588],
            ['wilaya_code' => '16', 'code' => '1602', 'postal_code' => '16001', 'en' => 'Sidi M\'Hamed', 'fr' => 'Sidi M\'Hamed', 'ar' => 'سيدي امحمد', 'lat' => 36.7562, 'lng' => 3.0569],
            ['wilaya_code' => '16', 'code' => '1604', 'postal_code' => '16008', 'en' => 'Bab El Oued', 'fr' => 'Bab El Oued', 'ar' => 'باب الوادي', 'lat' => 36.7903, 'lng' => 3.0489],
            ['wilaya_code' => '16', 'code' => '1607', 'postal_code' => '16012', 'en' => 'Hydra', 'fr' => 'Hydra', 'ar' => 'حيدرة', 'lat' => 36.7456, 'lng' => 3.0422],
            ['wilaya_code' => '16', 'code' => '1608', 'postal_code' => '16035', 'en' => 'El Biar', 'fr' => 'El Biar', 'ar' => 'الأبيار', 'lat' => 36.7692, 'lng' => 3.0306],
            ['wilaya_code' => '16', 'code' => '1609', 'postal_code' => '16065', 'en' => 'Kouba', 'fr' => 'Kouba', 'ar' => 'القبة', 'lat' => 36.7247, 'lng' => 3.0858],
            ['wilaya_code' => '16', 'code' => '1611', 'postal_code' => '16060', 'en' => 'Hussein Dey', 'fr' => 'Hussein Dey', 'ar' => 'حسين داي', 'lat' => 36.7442, 'lng' => 3.0906],
            ['wilaya_code' => '16', 'code' => '1612', 'postal_code' => '16033', 'en' => 'Bir Mourad Raïs', 'fr' => 'Bir Mourad Raïs', 'ar' => 'بئر مراد رايس', 'lat' => 36.7364, 'lng' => 3.0542],
            ['wilaya_code' => '16', 'code' => '1613', 'postal_code' => '16030', 'en' => 'Birkhadem', 'fr' => 'Birkhadem', 'ar' => 'بئر خادم', 'lat' => 36.7167, 'lng' => 3.0667],
            ['wilaya_code' => '16', 'code' => '1614', 'postal_code' => '16016', 'en' => 'Ben Aknoun', 'fr' => 'Ben Aknoun', 'ar' => 'بن عكنون', 'lat' => 36.7583, 'lng' => 3.0167],
            ['wilaya_code' => '16', 'code' => '1615', 'postal_code' => '16027', 'en' => 'Dely Ibrahim', 'fr' => 'Dely Ibrahim', 'ar' => 'دالي إبراهيم', 'lat' => 36.7500, 'lng' => 2.9833],
            ['wilaya_code' => '16', 'code' => '1618', 'postal_code' => '16038', 'en' => 'Cheraga', 'fr' => 'Chéraga', 'ar' => 'الشراقة', 'lat' => 36.7667, 'lng' => 2.9500],
            ['wilaya_code' => '16', 'code' => '1622', 'postal_code' => '16053', 'en' => 'Dar El Beïda', 'fr' => 'Dar El Beïda', 'ar' => 'الدار البيضاء', 'lat' => 36.7133, 'lng' => 3.2125],
            ['wilaya_code' => '16', 'code' => '1623', 'postal_code' => '16054', 'en' => 'Bab Ezzouar', 'fr' => 'Bab Ezzouar', 'ar' => 'باب الزوار', 'lat' => 36.7208, 'lng' => 3.1833],
            ['wilaya_code' => '16', 'code' => '1630', 'postal_code' => '16020', 'en' => 'Bordj El Kiffan', 'fr' => 'Bordj El Kiffan', 'ar' => 'برج الكيفان', 'lat' => 36.7486, 'lng' => 3.1914],

            // Oran (31)
            ['wilaya_code' => '31', 'code' => '3101', 'postal_code' => '31000', 'en' => 'Oran', 'fr' => 'Oran', 'ar' => 'وهران', 'lat' => 35.6971, 'lng' => -0.6308],
            ['wilaya_code' => '31', 'code' => '3102', 'postal_code' => '31200', 'en' => 'Gdyel', 'fr' => 'Gdyel', 'ar' => 'قديل', 'lat' => 35.7833, 'lng' => -0.4333],
            ['wilaya_code' => '31', 'code' => '3103', 'postal_code' => '31230', 'en' => 'Bir El Djir', 'fr' => 'Bir El Djir', 'ar' => 'بئر الجير', 'lat' => 35.7167, 'lng' => -0.5667],
            ['wilaya_code' => '31', 'code' => '3104', 'postal_code' => '31310', 'en' => 'Es Senia', 'fr' => 'Es Senia', 'ar' => 'السانية', 'lat' => 35.6500, 'lng' => -0.6333],
            ['wilaya_code' => '31', 'code' => '3105', 'postal_code' => '31280', 'en' => 'Arzew', 'fr' => 'Arzew', 'ar' => 'أرزيو', 'lat' => 35.8500, 'lng' => -0.3167],
            ['wilaya_code' => '31', 'code' => '3107', 'postal_code' => '31110', 'en' => 'Aïn El Turk', 'fr' => 'Aïn El Turk', 'ar' => 'عين الترك', 'lat' => 35.7500, 'lng' => -0.7500],
            ['wilaya_code' => '31', 'code' => '3110', 'postal_code' => '31240', 'en' => 'El Kerma', 'fr' => 'El Kerma', 'ar' => 'الكرمة', 'lat' => 35.6167, 'lng' => -0.5833],
            ['wilaya_code' => '31', 'code' => '3112', 'postal_code' => '31260', 'en' => 'Sidi Chami', 'fr' => 'Sidi Chami', 'ar' => 'سيدي الشحمي', 'lat' => 35.6667, 'lng' => -0.5333],

            // Constantine (25)
            ['wilaya_code' => '25', 'code' => '2501', 'postal_code' => '25000', 'en' => 'Constantine', 'fr' => 'Constantine', 'ar' => 'قسنطينة', 'lat' => 36.3650, 'lng' => 6.6147],
            ['wilaya_code' => '25', 'code' => '2502', 'postal_code' => '25100', 'en' => 'El Khroub', 'fr' => 'El Khroub', 'ar' => 'الخروب', 'lat' => 36.2667, 'lng' => 6.7000],
            ['wilaya_code' => '25', 'code' => '2503', 'postal_code' => '25210', 'en' => 'Aïn Smara', 'fr' => 'Aïn Smara', 'ar' => 'عين سمارة', 'lat' => 36.2667, 'lng' => 6.5000],
            ['wilaya_code' => '25', 'code' => '2504', 'postal_code' => '25130', 'en' => 'Hamma Bouziane', 'fr' => 'Hamma Bouziane', 'ar' => 'حامة بوزيان', 'lat' => 36.4000, 'lng' => 6.6000],
            ['wilaya_code' => '25', 'code' => '2505', 'postal_code' => '25120', 'en' => 'Zighoud Youcef', 'fr' => 'Zighoud Youcef', 'ar' => 'زيغود يوسف', 'lat' => 36.5333, 'lng' => 6.7167],
            ['wilaya_code' => '25', 'code' => '2506', 'postal_code' => '25240', 'en' => 'Didouche Mourad', 'fr' => 'Didouche Mourad', 'ar' => 'ديدوش مراد', 'lat' => 36.4500, 'lng' => 6.6333],

            // Blida (09)
            ['wilaya_code' => '09', 'code' => '0901', 'postal_code' => '09000', 'en' => 'Blida', 'fr' => 'Blida', 'ar' => 'البليدة', 'lat' => 36.4702, 'lng' => 2.8288],
            ['wilaya_code' => '09', 'code' => '0902', 'postal_code' => '09200', 'en' => 'Boufarik', 'fr' => 'Boufarik', 'ar' => 'بوفاريك', 'lat' => 36.5756, 'lng' => 2.9125],
            ['wilaya_code' => '09', 'code' => '0903', 'postal_code' => '09400', 'en' => 'Ouled Yaïch', 'fr' => 'Ouled Yaïch', 'ar' => 'أولاد يعيش', 'lat' => 36.5000, 'lng' => 2.8667],

            // Sétif (19)
            ['wilaya_code' => '19', 'code' => '1901', 'postal_code' => '19000', 'en' => 'Sétif', 'fr' => 'Sétif', 'ar' => 'سطيف', 'lat' => 36.1911, 'lng' => 5.4136],
            ['wilaya_code' => '19', 'code' => '1902', 'postal_code' => '19200', 'en' => 'El Eulma', 'fr' => 'El Eulma', 'ar' => 'العلمة', 'lat' => 36.1528, 'lng' => 5.6903],
            ['wilaya_code' => '19', 'code' => '1903', 'postal_code' => '19100', 'en' => 'Aïn Oulmene', 'fr' => 'Aïn Oulmène', 'ar' => 'عين ولمان', 'lat' => 35.9167, 'lng' => 5.3000],

            // Annaba (23)
            ['wilaya_code' => '23', 'code' => '2301', 'postal_code' => '23000', 'en' => 'Annaba', 'fr' => 'Annaba', 'ar' => 'عنابة', 'lat' => 36.9000, 'lng' => 7.7667],
            ['wilaya_code' => '23', 'code' => '2302', 'postal_code' => '23005', 'en' => 'El Bouni', 'fr' => 'El Bouni', 'ar' => 'البوني', 'lat' => 36.8667, 'lng' => 7.7333],
            ['wilaya_code' => '23', 'code' => '2303', 'postal_code' => '23200', 'en' => 'El Hadjar', 'fr' => 'El Hadjar', 'ar' => 'الحجار', 'lat' => 36.8000, 'lng' => 7.7333],
        ];

        foreach ($communes as $cData) {
            $cData['latitude'] = $cData['lat'];
            $cData['longitude'] = $cData['lng'];
            \App\Models\Commune::updateOrCreate(
                ['code' => $cData['code']],
                $cData
            );
        }

        // 3. Contact Platforms
        $contactPlatforms = [
            ['code' => 'phone', 'en' => 'Phone', 'fr' => 'Téléphone', 'ar' => 'الهاتف'],
            ['code' => 'whatsapp', 'en' => 'WhatsApp', 'fr' => 'WhatsApp', 'ar' => 'واتساب'],
            ['code' => 'viber', 'en' => 'Viber', 'fr' => 'Viber', 'ar' => 'فايبر'],
            ['code' => 'telegram', 'en' => 'Telegram', 'fr' => 'Telegram', 'ar' => 'تيليجرام'],
            ['code' => 'facebook', 'en' => 'Facebook', 'fr' => 'Facebook', 'ar' => 'فيسبوك'],
            ['code' => 'instagram', 'en' => 'Instagram', 'fr' => 'Instagram', 'ar' => 'إنستغرام'],
            ['code' => 'linkedin', 'en' => 'LinkedIn', 'fr' => 'LinkedIn', 'ar' => 'لينكد إن'],
            ['code' => 'website', 'en' => 'Website', 'fr' => 'Site Web', 'ar' => 'الموقع الإلكتروني'],
            ['code' => 'email', 'en' => 'Email', 'fr' => 'Email', 'ar' => 'البريد الإلكتروني'],
        ];

        foreach ($contactPlatforms as $data) {
            ContactPlatform::updateOrCreate(['code' => $data['code']], $data);
        }

        // 4. Statuses
        $statuses = [
            ['code' => 'pending', 'en' => 'Pending', 'fr' => 'En attente', 'ar' => 'قيد الانتظار'],
            ['code' => 'confirmed', 'en' => 'Confirmed', 'fr' => 'Confirmé', 'ar' => 'مؤكد'],
            ['code' => 'in_progress', 'en' => 'In Progress', 'fr' => 'En cours', 'ar' => 'جاري الموعد'],
            ['code' => 'completed', 'en' => 'Completed', 'fr' => 'Terminé', 'ar' => 'مكتمل'],
            ['code' => 'cancelled', 'en' => 'Cancelled', 'fr' => 'Annulé', 'ar' => 'ملغى'],
            ['code' => 'rescheduled', 'en' => 'Rescheduled', 'fr' => 'Reporté', 'ar' => 'مؤجل'],
            ['code' => 'no_show', 'en' => 'No Show', 'fr' => 'Absent', 'ar' => 'لم يحضر'],
        ];

        foreach ($statuses as $data) {
            Status::updateOrCreate(['code' => $data['code']], $data);
        }
    }
}
