<?php

namespace Database\Seeders;

use App\Models\ContactPlatform;
use App\Models\ServiceCatalog;
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
            $data['lat'] = $coords['lat'];
            $data['lng'] = $coords['lng'];
            Wilaya::updateOrCreate(['code' => $data['code']], $data);
        }

        // 2. Contact Platforms
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

        // 3. Statuses
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

        // 4. Service Catalogs
        $serviceCatalogs = [
            ['code' => 'consultation', 'source' => 'doctor', 'en' => 'General Consultation', 'fr' => 'Consultation Générale', 'ar' => 'استشارة طبية'],
            ['code' => 'specialist_consultation', 'source' => 'doctor', 'en' => 'Specialist Consultation', 'fr' => 'Consultation Spécialiste', 'ar' => 'استشارة أخصائي'],
            ['code' => 'checkup', 'source' => 'doctor', 'en' => 'Medical Checkup', 'fr' => 'Bilan de Santé', 'ar' => 'فحص طبي شامل'],
            ['code' => 'dental_scaling', 'source' => 'dentist', 'en' => 'Dental Scaling & Cleaning', 'fr' => 'Détartrage et Nettoyage', 'ar' => 'تنظيف وتلميع الأسنان'],
            ['code' => 'dental_extraction', 'source' => 'dentist', 'en' => 'Tooth Extraction', 'fr' => 'Extraction Dentaire', 'ar' => 'قلع الأسنان'],
            ['code' => 'radiology_xray', 'source' => 'center', 'en' => 'X-Ray Radiography', 'fr' => 'Radiographie Standard', 'ar' => 'أشعة سينية'],
            ['code' => 'radiology_mri', 'source' => 'center', 'en' => 'MRI Scan', 'fr' => 'IRM', 'ar' => 'رنين مغناطيسي'],
            ['code' => 'blood_test', 'source' => 'center', 'en' => 'Complete Blood Count (CBC)', 'fr' => 'NFS / Bilan Sanguin', 'ar' => 'تحليل دم كامل'],
            ['code' => 'physio_session', 'source' => 'physiotherapist', 'en' => 'Rehabilitation Session', 'fr' => 'Séance de Rééducation', 'ar' => 'جلسة ترويض طبي'],
        ];

        foreach ($serviceCatalogs as $srv) {
            ServiceCatalog::updateOrCreate(['code' => $srv['code']], $srv);
        }
    }
}
