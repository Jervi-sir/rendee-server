<?php

namespace Database\Seeders;

use App\Models\CenterCatalog;
use App\Models\ContactPlatform;
use App\Models\ServiceCatalog;
use App\Models\Speciality;
use App\Models\Status;
use App\Models\Wilaya;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Specialities
        $specialities = [
            ['code' => 'general', 'en' => 'General Practitioner', 'fr' => 'Médecin Généraliste', 'ar' => 'طبيب عام'],
            ['code' => 'cardiology', 'en' => 'Cardiology', 'fr' => 'Cardiologie', 'ar' => 'طب القلب'],
            ['code' => 'dermatology', 'en' => 'Dermatology', 'fr' => 'Dermatologie', 'ar' => 'طب الجلدية'],
            ['code' => 'pediatrics', 'en' => 'Pediatrics', 'fr' => 'Pédiatrie', 'ar' => 'طب الأطفال'],
            ['code' => 'orthopedics', 'en' => 'Orthopedics', 'fr' => 'Orthopédie', 'ar' => 'جراحة العظام'],
            ['code' => 'ophthalmology', 'en' => 'Ophthalmology', 'fr' => 'Ophtalmologie', 'ar' => 'طب العيون'],
            ['code' => 'neurology', 'en' => 'Neurology', 'fr' => 'Neurologie', 'ar' => 'طب الأعصاب'],
            ['code' => 'psychiatry', 'en' => 'Psychiatry', 'fr' => 'Psychiatrie', 'ar' => 'طب النفسي'],
        ];
        foreach ($specialities as $data) {
            Speciality::firstOrCreate(['code' => $data['code']], $data);
        }

        // Service Catalogs
        $services = [
            ['code' => 'consultation', 'en' => 'Consultation', 'fr' => 'Consultation', 'ar' => 'استشارة'],
            ['code' => 'checkup', 'en' => 'Medical Checkup', 'fr' => 'Bilan de Santé', 'ar' => 'فحص طبي'],
            ['code' => 'surgery', 'en' => 'Surgery', 'fr' => 'Chirurgie', 'ar' => 'جراحة'],
            ['code' => 'vaccination', 'en' => 'Vaccination', 'fr' => 'Vaccination', 'ar' => 'تلقيح'],
            ['code' => 'lab_test', 'en' => 'Lab Test', 'fr' => 'Analyse', 'ar' => 'تحليل مخبري'],
            ['code' => 'radiology', 'en' => 'Radiology', 'fr' => 'Radiologie', 'ar' => 'أشعة'],
            ['code' => 'dental', 'en' => 'Dental Care', 'fr' => 'Soins Dentaires', 'ar' => 'رعاية الأسنان'],
            ['code' => 'physiotherapy', 'en' => 'Physiotherapy', 'fr' => 'Kinésithérapie', 'ar' => 'علاج طبيعي'],
        ];
        foreach ($services as $data) {
            ServiceCatalog::firstOrCreate(['code' => $data['code']], $data);
        }

        // Wilayas (Algerian provinces - subset)
        $wilayas = [
            ['code' => '16', 'number' => '16', 'en' => 'Algiers', 'fr' => 'Alger', 'ar' => 'الجزائر'],
            ['code' => '31', 'number' => '31', 'en' => 'Oran', 'fr' => 'Oran', 'ar' => 'وهران'],
            ['code' => '06', 'number' => '06', 'en' => 'Bejaia', 'fr' => 'Bejaia', 'ar' => 'بجاية'],
            ['code' => '23', 'number' => '23', 'en' => 'Annaba', 'fr' => 'Annaba', 'ar' => 'عنابة'],
            ['code' => '13', 'number' => '13', 'en' => 'Tlemcen', 'fr' => 'Tlemcen', 'ar' => 'تلمسان'],
            ['code' => '19', 'number' => '19', 'en' => 'Setif', 'fr' => 'Sétif', 'ar' => 'سطيف'],
            ['code' => '25', 'number' => '25', 'en' => 'Constantine', 'fr' => 'Constantine', 'ar' => 'قسنطينة'],
            ['code' => '46', 'number' => '46', 'en' => 'Ain Temouchent', 'fr' => 'Ain Témouchent', 'ar' => 'عين تموشنت'],
        ];
        foreach ($wilayas as $data) {
            Wilaya::firstOrCreate(['code' => $data['code']], $data);
        }

        // Contact Platforms
        $platforms = [
            ['code' => 'phone', 'en' => 'Phone', 'fr' => 'Téléphone', 'ar' => 'هاتف'],
            ['code' => 'email', 'en' => 'Email', 'fr' => 'Email', 'ar' => 'البريد الإلكتروني'],
            ['code' => 'whatsapp', 'en' => 'WhatsApp', 'fr' => 'WhatsApp', 'ar' => 'واتساب'],
            ['code' => 'facebook', 'en' => 'Facebook', 'fr' => 'Facebook', 'ar' => 'فيسبوك'],
            ['code' => 'website', 'en' => 'Website', 'fr' => 'Site Web', 'ar' => 'موقع إلكتروني'],
        ];
        foreach ($platforms as $data) {
            ContactPlatform::firstOrCreate(['code' => $data['code']], $data);
        }

        // Center Catalogs
        $centerCatalogs = [
            ['code' => 'clinic', 'en' => 'Clinic', 'fr' => 'Clinique', 'ar' => 'عيادة'],
            ['code' => 'hospital', 'en' => 'Hospital', 'fr' => 'Hôpital', 'ar' => 'مستشفى'],
            ['code' => 'diagnostic', 'en' => 'Diagnostic Center', 'fr' => 'Centre de Diagnostic', 'ar' => 'مركز تشخيص'],
            ['code' => 'lab', 'en' => 'Laboratory', 'fr' => 'Laboratoire', 'ar' => 'مخبر'],
            ['code' => 'pharmacy', 'en' => 'Pharmacy', 'fr' => 'Pharmacie', 'ar' => 'صيدلية'],
        ];
        foreach ($centerCatalogs as $data) {
            CenterCatalog::firstOrCreate(['code' => $data['code']], $data);
        }

        // Statuses
        $statuses = [
            ['code' => 'pending', 'en' => 'Pending', 'fr' => 'En Attente', 'ar' => 'قيد الانتظار'],
            ['code' => 'confirmed', 'en' => 'Confirmed', 'fr' => 'Confirmé', 'ar' => 'مؤكد'],
            ['code' => 'cancelled', 'en' => 'Cancelled', 'fr' => 'Annulé', 'ar' => 'ملغي'],
            ['code' => 'completed', 'en' => 'Completed', 'fr' => 'Terminé', 'ar' => 'مكتمل'],
            ['code' => 'rescheduled', 'en' => 'Rescheduled', 'fr' => 'Reporté', 'ar' => 'معاد جدولته'],
            ['code' => 'no_show', 'en' => 'No Show', 'fr' => 'Absent', 'ar' => 'لم يحضر'],
        ];
        foreach ($statuses as $data) {
            Status::firstOrCreate(['code' => $data['code']], $data);
        }
    }
}
