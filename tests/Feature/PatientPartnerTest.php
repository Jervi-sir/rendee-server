<?php

use App\Models\Commune;
use App\Models\ContactPlatform;
use App\Models\Partner;
use App\Models\Profession;
use App\Models\Speciality;
use App\Models\User;
use App\Models\UserContact;
use App\Models\UserRole;
use App\Models\Wilaya;

test('patient can view partner details with profile image', function () {
    $user = User::factory()->create([
        'image_url' => '/storage/avatars/doctor.jpg',
    ]);

    $partner = Partner::create([
        'user_id' => $user->id,
        'name' => 'Dr. Amina Benali',
        'is_active' => true,
    ]);

    $response = $this->getJson(route('api.v1.patient.partners.show', $partner->id));

    $response->assertOk()
        ->assertJson([
            'id' => $partner->id,
            'name' => 'Dr. Amina Benali',
            'image_url' => url('/storage/avatars/doctor.jpg'),
            'profile_pic' => url('/storage/avatars/doctor.jpg'),
            'avatar' => url('/storage/avatars/doctor.jpg'),
            'image' => url('/storage/avatars/doctor.jpg'),
        ]);
});

test('handles partner with external profile image url', function () {
    $user = User::factory()->create([
        'image_url' => 'https://example.com/photos/doc.png',
    ]);

    $partner = Partner::create([
        'user_id' => $user->id,
        'name' => 'Dr. Karim Amrani',
        'is_active' => true,
    ]);

    $response = $this->getJson(route('api.v1.patient.partners.show', $partner->id));

    $response->assertOk()
        ->assertJson([
            'id' => $partner->id,
            'image_url' => 'https://example.com/photos/doc.png',
            'profile_pic' => 'https://example.com/photos/doc.png',
        ]);
});

test('handles partner without profile image', function () {
    $user = User::factory()->create([
        'image_url' => null,
    ]);

    $partner = Partner::create([
        'user_id' => $user->id,
        'name' => 'Clinic Al-Amal',
        'is_active' => true,
    ]);

    $response = $this->getJson(route('api.v1.patient.partners.show', $partner->id));

    $response->assertOk()
        ->assertJson([
            'id' => $partner->id,
            'image_url' => null,
            'profile_pic' => null,
            'avatar' => null,
            'image' => null,
        ]);
});

test('returns partner contacts correctly', function () {
    $user = User::factory()->create();

    ContactPlatform::firstOrCreate(['code' => 'phone'], ['en' => 'Phone', 'hex' => '#000000']);
    ContactPlatform::firstOrCreate(['code' => 'whatsapp'], ['en' => 'WhatsApp', 'hex' => '#25D366']);

    $partner = Partner::create([
        'user_id' => $user->id,
        'name' => 'Dr. Karim Amrani',
        'is_active' => true,
    ]);

    UserContact::create([
        'user_id' => $user->id,
        'platform_code' => 'phone',
        'url' => '0555123456',
    ]);

    UserContact::create([
        'user_id' => $user->id,
        'platform_code' => 'whatsapp',
        'url' => '0555987654',
    ]);

    $response = $this->getJson(route('api.v1.patient.partners.show', $partner->id));

    $response->assertOk()
        ->assertJsonCount(2, 'contacts')
        ->assertJsonFragment([
            'platform' => 'phone',
            'value' => '0555123456',
        ])
        ->assertJsonFragment([
            'platform' => 'whatsapp',
            'value' => '0555987654',
        ]);
});

test('returns partner speciality and profession without partner_type', function () {
    Profession::firstOrCreate(['code' => 'doctor'], ['en' => 'Doctor', 'ar' => 'طبيب', 'hex' => '#0ea5e9']);
    Speciality::firstOrCreate(['code' => 'cardiology'], ['en' => 'Cardiology', 'ar' => 'أمراض القلب', 'profession_code' => 'doctor']);

    $user = User::factory()->create();
    $partner = Partner::create([
        'user_id' => $user->id,
        'name' => 'Dr. Karim Amrani',
        'profession_code' => 'doctor',
        'speciality_code' => 'cardiology',
        'is_active' => true,
    ]);

    $response = $this->getJson(route('api.v1.patient.partners.show', $partner->id));

    $response->assertOk()
        ->assertJson([
            'id' => $partner->id,
            'profession' => [
                'code' => 'doctor',
                'label' => 'طبيب',
            ],
            'speciality' => [
                'code' => 'cardiology',
                'label' => 'أمراض القلب',
            ],
        ])
        ->assertJsonMissingPath('partner_type');
});

test('returns 404 if partner not found', function () {
    $response = $this->getJson(route('api.v1.patient.partners.show', 99999));

    $response->assertNotFound();
});

test('patient onboarding updates profile with commune_code successfully', function () {
    UserRole::firstOrCreate(['code' => 'patient'], ['en' => 'Patient']);
    $wilaya = Wilaya::firstOrCreate(['code' => '01'], ['en' => 'Adrar', 'ar' => 'أدرار']);
    $commune = Commune::firstOrCreate(
        ['code' => '1087'],
        ['wilaya_code' => '01', 'ar' => 'أولاد أحمد تيمي', 'en' => 'Ouled Ahmed Timmi']
    );

    $user = User::factory()->create([
        'user_role_code' => 'patient',
    ]);

    $response = $this->actingAs($user)->postJson(route('api.v1.patient.onboarding.update'), [
        'date_of_birth' => '1998-06-08',
        'gender' => 'male',
        'wilaya_code' => '01',
        'commune_code' => '1087',
        'address' => 'Test Address',
        'blood_type' => 'O+',
    ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'is_completed' => true,
        ]);

    $patient = $user->fresh()->patient;
    expect($patient)->not->toBeNull()
        ->and($patient->commune_id)->toBe($commune->id)
        ->and($patient->commune_code)->toBe('1087')
        ->and($patient->wilaya_code)->toBe('01');
});

test('patient profile update handles commune_code successfully', function () {
    UserRole::firstOrCreate(['code' => 'patient'], ['en' => 'Patient']);
    $wilaya = Wilaya::firstOrCreate(['code' => '01'], ['en' => 'Adrar', 'ar' => 'أدرار']);
    $commune = Commune::firstOrCreate(
        ['code' => '1087'],
        ['wilaya_code' => '01', 'ar' => 'أولاد أحمد تيمي', 'en' => 'Ouled Ahmed Timmi']
    );

    $user = User::factory()->create([
        'user_role_code' => 'patient',
    ]);

    $response = $this->actingAs($user)->putJson(route('api.v1.patient.profile.update'), [
        'full_name' => 'Test Patient',
        'date_of_birth' => '1998-06-08',
        'gender' => 'male',
        'wilaya_code' => '01',
        'commune_code' => '1087',
        'address' => 'Updated Address',
        'blood_type' => 'A+',
    ]);

    $response->assertOk()
        ->assertJson([
            'message' => 'Profile updated successfully.',
        ]);

    $patient = $user->fresh()->patient;
    expect($patient)->not->toBeNull()
        ->and($patient->commune_id)->toBe($commune->id)
        ->and($patient->commune_code)->toBe('1087');
});

test('partner can load dashboard without errors', function () {
    UserRole::firstOrCreate(['code' => 'doctor'], ['en' => 'Doctor']);
    Profession::firstOrCreate(['code' => 'doctor'], ['en' => 'Doctor', 'ar' => 'طبيب', 'hex' => '#0ea5e9']);
    Speciality::firstOrCreate(['code' => 'general'], ['en' => 'General', 'ar' => 'عام', 'profession_code' => 'doctor']);

    $user = User::factory()->create([
        'user_role_code' => 'doctor',
    ]);

    $partner = Partner::create([
        'user_id' => $user->id,
        'name' => 'Dr. Dashboard Test',
        'profession_code' => 'doctor',
        'speciality_code' => 'general',
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->getJson('/api/v1/partner/dashboard');

    $response->assertOk()
        ->assertJsonStructure([
            'header' => ['partner_name', 'professional_name', 'speciality', 'date_label'],
            'stats' => ['pending', 'in_progress', 'confirmed', 'completed', 'cancelled', 'today_appointments', 'completed_today', 'total_completed', 'pending_requests'],
            'stats_list',
            'appointments',
            'current_page',
            'total',
        ]);
});
