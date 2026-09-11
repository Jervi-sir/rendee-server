<?php

namespace Database\Seeders;

use App\Models\Commune;
use App\Models\LikedPartner;
use App\Models\Partner;
use App\Models\PartnerSchedule;
use App\Models\PartnerService;
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
        // 1. Professions
        $professions = [
            ['code' => 'doctor', 'en' => 'Doctor', 'fr' => 'Médecin', 'ar' => 'طبيب', 'hex' => '#0ea5e9'],
            ['code' => 'dentist', 'en' => 'Dentist', 'fr' => 'Dentiste', 'ar' => 'طبيب أسنان', 'hex' => '#10b981'],
            ['code' => 'pharmacy', 'en' => 'Pharmacy', 'fr' => 'Pharmacie', 'ar' => 'صيدلية', 'hex' => '#059669'],
            ['code' => 'psychologist', 'en' => 'Psychologist', 'fr' => 'Psychologue', 'ar' => 'أخصائي نفساني', 'hex' => '#8b5cf6'],
            ['code' => 'nurse', 'en' => 'Nurse', 'fr' => 'Infirmier', 'ar' => 'ممرض', 'hex' => '#f59e0b'],
            ['code' => 'physiotherapist', 'en' => 'Physiotherapist', 'fr' => 'Kinésithérapeute', 'ar' => 'أخصائي علاج طبيعي', 'hex' => '#ec4899'],
            ['code' => 'professional', 'en' => 'Healthcare Professional', 'fr' => 'Professionnel de santé', 'ar' => 'مهني صحي', 'hex' => '#6366f1'],
        ];

        foreach ($professions as $prof) {
            Profession::updateOrCreate(['code' => $prof['code']], $prof);
        }

        // 2. Specialities (Linked to profession_code)
        $specialities = [
            ['code' => 'cardiology', 'profession_code' => 'doctor', 'en' => 'Cardiology', 'fr' => 'Cardiologie', 'ar' => 'طب القلب'],
            ['code' => 'dermatology', 'profession_code' => 'doctor', 'en' => 'Dermatology', 'fr' => 'Dermatologie', 'ar' => 'طب الجلد'],
            ['code' => 'endocrinology_diabetology', 'profession_code' => 'doctor', 'en' => 'Endocrinology & Diabetology', 'fr' => 'Endocrinologie et diabétologie', 'ar' => 'الغدد والسكري'],
            ['code' => 'gastroenterology', 'profession_code' => 'doctor', 'en' => 'Gastroenterology', 'fr' => 'Gastro-entérologie', 'ar' => 'الجهاز الهضمي'],
            ['code' => 'general', 'profession_code' => 'doctor', 'en' => 'General Practitioner', 'fr' => 'Médecin Généraliste', 'ar' => 'طبيب عام'],
            ['code' => 'gynecology', 'profession_code' => 'doctor', 'en' => 'Obstetrics & Gynecology', 'fr' => 'Gynécologie-obstétrique', 'ar' => 'طب النساء والتوليد'],
            ['code' => 'kinésithérapie', 'profession_code' => 'professional', 'en' => 'Physiotherapy', 'fr' => 'Kinésithérapie', 'ar' => 'العلاج الطبيعي وإعادة التأهيل'],
            ['code' => 'medical_oncology', 'profession_code' => 'doctor', 'en' => 'Medical Oncology', 'fr' => 'Oncologie médicale', 'ar' => 'طب الأورام'],
            ['code' => 'nephrology', 'profession_code' => 'doctor', 'en' => 'Nephrology', 'fr' => 'Néphrologie', 'ar' => 'أمراض الكلى'],
            ['code' => 'neurology', 'profession_code' => 'doctor', 'en' => 'Neurology', 'fr' => 'Neurologie', 'ar' => 'طب الأعصاب'],
            ['code' => 'nutrition', 'profession_code' => 'professional', 'en' => 'Nutrition', 'fr' => 'Nutrition', 'ar' => 'التغذية'],
            ['code' => 'ophthalmology', 'profession_code' => 'doctor', 'en' => 'Ophthalmology', 'fr' => 'Ophtalmologie', 'ar' => 'طب العيون'],
            ['code' => 'orl', 'profession_code' => 'doctor', 'en' => 'ORL', 'fr' => 'ENT', 'ar' => 'الأنف والأذن والحنجرة'],
            ['code' => 'orthopedics', 'profession_code' => 'doctor', 'en' => 'Orthopedics & Traumatology', 'fr' => 'Orthopédie-traumatologie', 'ar' => 'العظام والمفاصل'],
            ['code' => 'orthophonie', 'profession_code' => 'professional', 'en' => 'Speech & Language Therapy', 'fr' => 'Orthophonie', 'ar' => 'ارطوفونيا'],
            ['code' => 'pediatrics', 'profession_code' => 'doctor', 'en' => 'Pediatrics', 'fr' => 'Pédiatrie', 'ar' => 'طب الأطفال'],
            ['code' => 'psychiatie', 'profession_code' => 'doctor', 'en' => 'Psychiatry', 'fr' => 'Psychiatrie', 'ar' => 'الطب النفسي'],
            ['code' => 'pulmonology', 'profession_code' => 'doctor', 'en' => 'Pulmonology', 'fr' => 'Pneumologie', 'ar' => 'أمراض الرئة'],
            ['code' => 'rheumatology', 'profession_code' => 'doctor', 'en' => 'Rheumatology', 'fr' => 'Rhumatologie', 'ar' => 'الروماتيزم'],
            ['code' => 'urology', 'profession_code' => 'doctor', 'en' => 'Urology', 'fr' => 'Urologie', 'ar' => 'المسالك البولية'],
        ];

        foreach ($specialities as $spec) {
            Speciality::updateOrCreate(['code' => $spec['code']], $spec);
        }

        // 3. Partners
        $partnerUsers = User::where('user_role_code', UserRole::PARTNER)->get();

        if ($partnerUsers->isEmpty()) {
            $partnerUsers = User::where('user_role_code', '!=', UserRole::PATIENT)->get();
        }

        $allWilayas = Wilaya::all();
        $professionsList = Profession::all();

        foreach ($partnerUsers as $user) {
            $wilaya = $allWilayas->isNotEmpty() ? $allWilayas->random() : null;
            $commune = $wilaya ? Commune::where('wilaya_code', $wilaya->code)->inRandomOrder()->first() : null;

            if (str_contains($user->email ?? '', 'pharmacy')) {
                $professionCode = 'pharmacy';
                $specialityCode = null;
                $partnerName = $user->name;
            } elseif (str_contains($user->email ?? '', 'dentist')) {
                $professionCode = 'dentist';
                $specialityCode = null;
                $partnerName = str_starts_with($user->name, 'Dr.') ? $user->name : 'Dr. '.$user->name;
            } elseif (str_contains($user->email ?? '', 'center') || str_contains($user->email ?? '', 'clinique')) {
                $professionCode = 'doctor';
                $availableSpecialities = Speciality::where('profession_code', $professionCode)->get();
                $specialityCode = $availableSpecialities->isNotEmpty() ? $availableSpecialities->random()->code : null;
                $partnerName = $user->name;
            } else {
                $profession = $professionsList->isNotEmpty() ? $professionsList->random() : null;
                $professionCode = $profession?->code ?? 'doctor';

                $availableSpecialities = Speciality::where('profession_code', $professionCode)->get();
                $specialityCode = $availableSpecialities->isNotEmpty() ? $availableSpecialities->random()->code : null;

                $partnerName = $professionCode === 'pharmacy'
                    ? (str_starts_with($user->name, 'Pharmacie ') ? $user->name : 'Pharmacie '.$user->name)
                    : (str_starts_with($user->name, 'Dr.') ? $user->name : 'Dr. '.$user->name);
            }

            $partner = Partner::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'name' => $partnerName,
                    'profession_code' => $professionCode,
                    'speciality_code' => $specialityCode,
                    'custom_speciality' => null,
                    'wilaya_code' => $wilaya?->code,
                    'commune_id' => $commune?->id,
                    'license_number' => 'LIC-'.fake()->numerify('######'),
                    'years_experience' => (string) fake()->numberBetween(3, 28),
                    'phone_public' => $user->phone_number ?? ('05'.fake()->numerify('########')),
                    'bio' => 'Professionnel de santé qualifié avec plusieurs années d\'expérience au service des patients.',
                    'address' => fake()->streetAddress(),
                    'city' => $commune?->fr ?? $commune?->en ?? $wilaya?->en ?? 'Alger',
                    'lat' => fake()->latitude(35.5, 36.9),
                    'lng' => fake()->longitude(2.5, 7.5),
                    'is_available' => true,
                    'emergency_24_7' => fake()->boolean(25),
                    'is_on_duty' => fake()->boolean(20),
                    'is_active' => true,
                ]
            );

            // 4. Partner Schedules (Sunday to Thursday default open, Friday/Saturday optional)
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
                        'morning_start_time' => $isOpen ? '08:30:00' : null,
                        'morning_end_time' => $isOpen ? '12:30:00' : null,
                        'morning_is_active' => $isOpen,
                        'evening_start_time' => $isOpen ? '13:30:00' : null,
                        'evening_end_time' => $isOpen ? '17:00:00' : null,
                        'evening_is_active' => $isOpen,
                        'is_active' => $isOpen,
                    ]
                );
            }

            // 5. Partner Services (1 to 3 services for each partner)
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

        // 6. LikedPartners (Seed random user likes for partners)
        $allPatients = User::where('user_role_code', UserRole::PATIENT)->get();
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
