<?php

namespace Database\Seeders;

use App\Models\CenterCatalog;
use App\Models\LikedPartner;
use App\Models\Partner;
use App\Models\PartnerSchedule;
use App\Models\PartnerService;
use App\Models\PartnerType;
use App\Models\Profession;
use App\Models\ServiceCatalog;
use App\Models\Speciality;
use App\Models\User;
use App\Models\UserRole;
use App\Models\Wilaya;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PartnerSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // 1. Partner Types
        $partnerTypes = [
            ['code' => PartnerType::DOCTOR, 'en' => 'Doctor', 'fr' => 'Médecin', 'ar' => 'طبيب'],
            ['code' => PartnerType::DENTIST, 'en' => 'Dentist', 'fr' => 'Dentiste', 'ar' => 'طبيب أسنان'],
            ['code' => PartnerType::PHARMACIST, 'en' => 'Pharmacist', 'fr' => 'Pharmacien', 'ar' => 'صيدلي'],
            ['code' => PartnerType::CENTER, 'en' => 'Medical Center', 'fr' => 'Centre Médical', 'ar' => 'مركز طبي'],
            ['code' => PartnerType::PSY, 'en' => 'Psychologist', 'fr' => 'Psychologue', 'ar' => 'أخصائي نفساني'],
            ['code' => PartnerType::PROFESSIONAL, 'en' => 'Professional', 'fr' => 'Professionnel', 'ar' => 'أخصائي'],
        ];

        foreach ($partnerTypes as $type) {
            PartnerType::updateOrCreate(['code' => $type['code']], $type);
        }

        // 2. Professions
        $professions = [
            ['code' => 'doctor', 'partner_type_code' => PartnerType::DOCTOR, 'en' => 'Doctor', 'fr' => 'Médecin', 'ar' => 'طبيب', 'hex' => '#0ea5e9'],
            ['code' => 'dentist', 'partner_type_code' => PartnerType::DENTIST, 'en' => 'Dentist', 'fr' => 'Dentiste', 'ar' => 'طبيب أسنان', 'hex' => '#10b981'],
            ['code' => 'psychologist', 'partner_type_code' => PartnerType::PSY, 'en' => 'Psychologist', 'fr' => 'Psychologue', 'ar' => 'أخصائي نفساني', 'hex' => '#8b5cf6'],
            ['code' => 'nurse', 'partner_type_code' => PartnerType::PROFESSIONAL, 'en' => 'Nurse', 'fr' => 'Infirmier', 'ar' => 'ممرض', 'hex' => '#f59e0b'],
            ['code' => 'physiotherapist', 'partner_type_code' => PartnerType::PROFESSIONAL, 'en' => 'Physiotherapist', 'fr' => 'Kinésithérapeute', 'ar' => 'أخصائي علاج طبيعي', 'hex' => '#ec4899'],
        ];

        foreach ($professions as $prof) {
            Profession::updateOrCreate(['code' => $prof['code']], $prof);
        }

        // 3. Specialities (Isolated & linked to profession_code)
        $specialities = [
            ['code' => 'general', 'profession_code' => 'doctor', 'en' => 'General Practitioner', 'fr' => 'Médecin Généraliste', 'ar' => 'طبيب عام'],
            ['code' => 'cardiology', 'profession_code' => 'doctor', 'en' => 'Cardiology', 'fr' => 'Cardiologie', 'ar' => 'طب القلب'],
            ['code' => 'dermatology', 'profession_code' => 'doctor', 'en' => 'Dermatology', 'fr' => 'Dermatologie', 'ar' => 'طب الجلدية'],
            ['code' => 'pediatrics', 'profession_code' => 'doctor', 'en' => 'Pediatrics', 'fr' => 'Pédiatrie', 'ar' => 'طب الأطفال'],
            ['code' => 'orthopedics', 'profession_code' => 'doctor', 'en' => 'Orthopedics', 'fr' => 'Orthopédie', 'ar' => 'جراحة العظام'],
            ['code' => 'ophthalmology', 'profession_code' => 'doctor', 'en' => 'Ophthalmology', 'fr' => 'Ophtalmologie', 'ar' => 'طب العيون'],
            ['code' => 'neurology', 'profession_code' => 'doctor', 'en' => 'Neurology', 'fr' => 'Neurologie', 'ar' => 'طب الأعصاب'],
            ['code' => 'gynecology', 'profession_code' => 'doctor', 'en' => 'Gynecology & Obstetrics', 'fr' => 'Gynécologie Obstétrique', 'ar' => 'أمراض النساء والتوليد'],
            ['code' => 'dentistry', 'profession_code' => 'dentist', 'en' => 'General Dentistry', 'fr' => 'Dentisterie Générale', 'ar' => 'طب أسنان عام'],
            ['code' => 'orthodontics', 'profession_code' => 'dentist', 'en' => 'Orthodontics', 'fr' => 'Orthodontie', 'ar' => 'تقويم الأسنان'],
            ['code' => 'implantology', 'profession_code' => 'dentist', 'en' => 'Implantology', 'fr' => 'Implantologie', 'ar' => 'زراعة الأسنان'],
            ['code' => 'psychiatry', 'profession_code' => 'psychologist', 'en' => 'Psychiatry', 'fr' => 'Psychiatrie', 'ar' => 'طب النفسي'],
            ['code' => 'clinical_psy', 'profession_code' => 'psychologist', 'en' => 'Clinical Psychology', 'fr' => 'Psychologie Clinique', 'ar' => 'علم النفس العيادي'],
            ['code' => 'kinesitherapy', 'profession_code' => 'physiotherapist', 'en' => 'Physiotherapy', 'fr' => 'Kinésithérapie', 'ar' => 'علاج طبيعي'],
        ];

        foreach ($specialities as $spec) {
            Speciality::updateOrCreate(['code' => $spec['code']], $spec);
        }

        // 4. Center Catalogs
        $centerCatalogs = [
            ['code' => 'clinic', 'en' => 'Private Clinic', 'fr' => 'Clinique Privée', 'ar' => 'عيادة خاصة'],
            ['code' => 'hospital', 'en' => 'Hospital', 'fr' => 'Hôpital', 'ar' => 'مستشفى'],
            ['code' => 'radiology_center', 'en' => 'Radiology & Imaging Center', 'fr' => 'Centre d\'Imagerie Médicale', 'ar' => 'مركز الأشعة والتصوير'],
            ['code' => 'laboratory', 'en' => 'Medical Analysis Laboratory', 'fr' => 'Laboratoire d\'Analyses Médicales', 'ar' => 'مخبر التحاليل الطبية'],
            ['code' => 'polyclinic', 'en' => 'Polyclinic', 'fr' => 'Polyclinique', 'ar' => 'عيادة متعددة الخدمات'],
            ['code' => 'dental_center', 'en' => 'Dental Center', 'fr' => 'Centre Dentaire', 'ar' => 'مركز طب الأسنان'],
        ];

        foreach ($centerCatalogs as $cat) {
            CenterCatalog::updateOrCreate(['code' => $cat['code']], $cat);
        }

        // 5. Service Catalogs
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

        // 6. Partners (Isolated: picks random partner users and random catalogue items)
        $partnerUsers = User::whereIn('user_role_code', ['partner', 'doctor', 'dentist', 'center', 'pharmacist', 'psy'])->get();

        if ($partnerUsers->isEmpty()) {
            $partnerUsers = User::where('user_role_code', '!=', UserRole::PATIENT)->get();
        }

        $allWilayas = Wilaya::all();
        $allCenterCatalogs = CenterCatalog::all();

        foreach ($partnerUsers as $user) {
            $wilaya = $allWilayas->isNotEmpty() ? $allWilayas->random() : null;

            $partnerTypeCode = match ($user->user_role_code) {
                'doctor' => PartnerType::DOCTOR,
                'dentist' => PartnerType::DENTIST,
                'center' => PartnerType::CENTER,
                'pharmacist' => PartnerType::PHARMACIST,
                'psy' => PartnerType::PSY,
                default => fake()->randomElement([PartnerType::DOCTOR, PartnerType::DENTIST, PartnerType::CENTER, PartnerType::PHARMACIST]),
            };

            $professionCode = null;
            $specialityCode = null;
            $centerCatalogCode = null;

            if ($partnerTypeCode === PartnerType::CENTER) {
                $centerCatalogCode = $allCenterCatalogs->isNotEmpty() ? $allCenterCatalogs->random()->code : 'clinic';
            } elseif ($partnerTypeCode === PartnerType::PHARMACIST) {
                // Pharmacies don't strictly require a medical profession code
            } else {
                $professionCode = match ($partnerTypeCode) {
                    PartnerType::DOCTOR => 'doctor',
                    PartnerType::DENTIST => 'dentist',
                    PartnerType::PSY => 'psychologist',
                    default => 'doctor',
                };

                $availableSpecialities = Speciality::where('profession_code', $professionCode)->get();
                $specialityCode = $availableSpecialities->isNotEmpty() ? $availableSpecialities->random()->code : null;
            }

            $partnerName = match ($partnerTypeCode) {
                PartnerType::CENTER => 'Clinique '.fake()->company(),
                PartnerType::PHARMACIST => 'Pharmacie '.$user->name,
                default => 'Dr. '.$user->name,
            };

            $partner = Partner::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'partner_type_code' => $partnerTypeCode,
                    'name' => $partnerName,
                    'profession_code' => $professionCode,
                    'speciality_code' => $specialityCode,
                    'custom_speciality' => null,
                    'center_catalog_code' => $centerCatalogCode,
                    'wilaya_code' => $wilaya?->code,
                    'license_number' => 'LIC-'.fake()->numerify('######'),
                    'years_experience' => (string) fake()->numberBetween(3, 28),
                    'phone_public' => $user->phone_number ?? ('05'.fake()->numerify('########')),
                    'bio' => 'Professionnel de santé qualifié avec plusieurs années d\'expérience au service des patients.',
                    'address' => fake()->streetAddress(),
                    'city' => $wilaya?->en ?? 'Alger',
                    'latitude' => fake()->latitude(35.5, 36.9),
                    'longitude' => fake()->longitude(2.5, 7.5),
                    'is_available' => true,
                    'emergency_24_7' => fake()->boolean(25),
                    'is_on_duty' => fake()->boolean(20),
                    'is_active' => true,
                ]
            );

            // 7. Partner Schedules (Isolated: Sunday to Thursday default open, Friday/Saturday optional)
            for ($day = 0; $day <= 6; $day++) {
                $isOpen = $day >= 0 && $day <= 4; // Sunday to Thursday open
                PartnerSchedule::updateOrCreate(
                    [
                        'partner_id' => $partner->id,
                        'day_of_week' => $day,
                    ],
                    [
                        'start_time' => $isOpen ? '08:30:00' : null,
                        'end_time' => $isOpen ? '17:00:00' : null,
                        'is_active' => $isOpen,
                    ]
                );
            }

            // 8. Partner Services (Isolated: 1 to 3 services for each partner)
            $availableServiceCatalogs = ServiceCatalog::all();
            if ($availableServiceCatalogs->isNotEmpty()) {
                $sampleCatalogs = $availableServiceCatalogs->random(min(3, $availableServiceCatalogs->count()));
                foreach ($sampleCatalogs as $svcCat) {
                    PartnerService::updateOrCreate(
                        [
                            'partner_id' => $partner->id,
                            'service_catalog_code' => $svcCat->code,
                        ],
                        [
                            'name' => $svcCat->fr ?? $svcCat->en ?? $svcCat->code,
                            'description' => 'Service médical de '.($svcCat->fr ?? $svcCat->en),
                            'price' => fake()->randomElement([1500, 2000, 2500, 3000, 4000, 5000]),
                            'duration_minutes' => fake()->randomElement([20, 30, 45, 60]),
                            'is_active' => true,
                        ]
                    );
                }
            }
        }

        // 9. LikedPartners (Seed random user likes for partners)
        $allPatients = User::where('user_role_code', 'patient')->get();
        $allPartners = Partner::all();

        if ($allPatients->isNotEmpty() && $allPartners->isNotEmpty()) {
            foreach ($allPatients as $patientUser) {
                $likedPartners = $allPartners->random(min(3, $allPartners->count()));
                foreach ($likedPartners as $likedPartner) {
                    LikedPartner::firstOrCreate([
                        'user_id' => $patientUser->id,
                        'partner_id' => $likedPartner->id,
                    ]);
                }
            }
        }
    }
}
