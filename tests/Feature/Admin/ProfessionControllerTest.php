<?php

use App\Models\PartnerType;
use App\Models\Profession;
use App\Models\Speciality;
use App\Models\User;

test('non-admin cannot access admin catalog routes', function () {
    $nonAdmin = User::factory()->patient()->create();

    $response = $this->actingAs($nonAdmin)
        ->get(route('admin.catalogs.professions.index'));

    $response->assertForbidden();
});

test('authenticated admin user can view professions catalog page', function () {
    $user = User::factory()->admin()->create();
    $partnerType = PartnerType::create([
        'code' => 'doctor',
        'en' => 'Doctor',
        'fr' => 'Médecin',
        'ar' => 'طبيب',
    ]);

    Profession::create([
        'code' => 'general_practitioner',
        'partner_type_code' => 'doctor',
        'en' => 'General Practitioner',
        'fr' => 'Généraliste',
        'ar' => 'طبيب عام',
        'hex' => '#10B981',
    ]);

    $response = $this->actingAs($user)
        ->get(route('admin.catalogs.professions.index'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/catalogs/professions')
            ->has('professions.data', 1)
            ->has('partnerTypes', 1)
        );
});

test('can filter professions by partner type and search query', function () {
    $user = User::factory()->admin()->create();

    $docType = PartnerType::create(['code' => 'doctor', 'en' => 'Doctor', 'fr' => 'Doc', 'ar' => 'طبيب']);
    $dentistType = PartnerType::create(['code' => 'dentist', 'en' => 'Dentist', 'fr' => 'Dentiste', 'ar' => 'طبيب أسنان']);

    Profession::create([
        'code' => 'cardiologist',
        'partner_type_code' => $docType->code,
        'en' => 'Cardiologist',
        'hex' => '#FF0000',
    ]);

    Profession::create([
        'code' => 'orthodontist',
        'partner_type_code' => $dentistType->code,
        'en' => 'Orthodontist',
        'hex' => '#00FF00',
    ]);

    // Filter by partner_type=dentist
    $response = $this->actingAs($user)
        ->get(route('admin.catalogs.professions.index', ['partner_type' => 'dentist']));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('professions.data', 1)
            ->where('professions.data.0.code', 'orthodontist')
        );

    // Search by 'Cardio'
    $searchResponse = $this->actingAs($user)
        ->get(route('admin.catalogs.professions.index', ['search' => 'Cardio']));

    $searchResponse->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('professions.data', 1)
            ->where('professions.data.0.code', 'cardiologist')
        );
});

test('can store and update profession', function () {
    $user = User::factory()->admin()->create();
    $partnerType = PartnerType::create(['code' => 'doctor', 'en' => 'Doctor', 'fr' => 'Doc', 'ar' => 'طبيب']);

    // Store
    $storeResponse = $this->actingAs($user)
        ->postJson(route('admin.catalogs.professions.store'), [
            'code' => 'dermatologist',
            'partner_type_code' => $partnerType->code,
            'en' => 'Dermatologist',
            'fr' => 'Dermatologue',
            'ar' => 'طبيب أمراض جلدية',
            'hex' => '#6366F1',
        ]);

    $storeResponse->assertCreated()
        ->assertJsonFragment(['code' => 'dermatologist']);

    $this->assertDatabaseHas('professions', [
        'code' => 'dermatologist',
        'en' => 'Dermatologist',
    ]);

    // Update
    $updateResponse = $this->actingAs($user)
        ->putJson(route('admin.catalogs.professions.update', 'dermatologist'), [
            'partner_type_code' => $partnerType->code,
            'en' => 'Dermatologist Updated',
            'fr' => 'Dermatologue Mis à jour',
            'ar' => 'طبيب جلدية',
            'hex' => '#4F46E5',
        ]);

    $updateResponse->assertOk()
        ->assertJsonFragment(['en' => 'Dermatologist Updated']);

    $this->assertDatabaseHas('professions', [
        'code' => 'dermatologist',
        'en' => 'Dermatologist Updated',
    ]);
});

test('can fetch, store and delete specialities under profession', function () {
    $user = User::factory()->admin()->create();
    $profession = Profession::create([
        'code' => 'surgeon',
        'en' => 'Surgeon',
        'hex' => '#EF4444',
    ]);

    // Store speciality
    $storeSpec = $this->actingAs($user)
        ->postJson(route('admin.catalogs.professions.specialities.store', $profession->code), [
            'code' => 'neuro_surgery',
            'en' => 'Neurosurgery',
            'fr' => 'Neurochirurgie',
            'ar' => 'جراحة المخ والأعصاب',
        ]);

    $storeSpec->assertOk()
        ->assertJsonFragment(['code' => 'neuro_surgery']);

    $this->assertDatabaseHas('specialities', [
        'code' => 'neuro_surgery',
        'profession_code' => 'surgeon',
    ]);

    // Fetch specialities for profession
    $getSpec = $this->actingAs($user)
        ->getJson(route('admin.catalogs.professions.specialities', $profession->code));

    $getSpec->assertOk()
        ->assertJsonCount(1, 'specialities');

    // Delete speciality
    $delSpec = $this->actingAs($user)
        ->deleteJson(route('admin.catalogs.specialities.destroy', 'neuro_surgery'));

    $delSpec->assertOk();

    $this->assertDatabaseMissing('specialities', [
        'code' => 'neuro_surgery',
    ]);
});
