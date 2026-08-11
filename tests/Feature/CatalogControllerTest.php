<?php

use App\Models\ContactPlatform;
use App\Models\ProfessionalSpeciality;
use App\Models\ServiceCatalog;
use App\Models\Status;
use App\Models\Wilaya;

test('returns 422 when no includes are specified', function () {
    $this->getJson('/api/public/v1/catalogs')
        ->assertStatus(422)
        ->assertJsonStructure(['message']);
});

test('returns only requested catalog data', function () {
    ProfessionalSpeciality::create(['code' => 'dermatology', 'en' => 'Dermatology']);
    Wilaya::create(['code' => '16', 'number' => '16', 'en' => 'Algiers']);

    $response = $this->getJson('/api/public/v1/catalogs?includes=professional_specialities,wilayas');

    $response->assertOk()
        ->assertJsonCount(1, 'professional_specialities')
        ->assertJsonCount(1, 'wilayas')
        ->assertJsonMissing(['service_catalogs']);
});

test('ignores invalid include keys', function () {
    ProfessionalSpeciality::create(['code' => 'cardiology', 'en' => 'Cardiology']);

    $response = $this->getJson('/api/public/v1/catalogs?includes=professional_specialities,invalid_key');

    $response->assertOk()
        ->assertJsonCount(1, 'professional_specialities');
});

test('filters service_catalogs by source', function () {
    ServiceCatalog::create(['code' => 'consultation', 'source' => 'doctor', 'en' => 'Consultation']);
    ServiceCatalog::create(['code' => 'xray', 'source' => 'center', 'en' => 'X-Ray']);

    $response = $this->getJson('/api/public/v1/catalogs?includes=service_catalogs&source=doctor');

    $response->assertOk()
        ->assertJsonCount(1, 'service_catalogs')
        ->assertJsonFragment(['code' => 'consultation']);
});

test('returns all service_catalogs when no source filter', function () {
    ServiceCatalog::create(['code' => 'consultation', 'source' => 'doctor', 'en' => 'Consultation']);
    ServiceCatalog::create(['code' => 'xray', 'source' => 'center', 'en' => 'X-Ray']);

    $response = $this->getJson('/api/public/v1/catalogs?includes=service_catalogs');

    $response->assertOk()
        ->assertJsonCount(2, 'service_catalogs');
});

test('returns multiple catalog types in a single request', function () {
    ProfessionalSpeciality::create(['code' => 'dermatology', 'en' => 'Dermatology']);
    ContactPlatform::create(['code' => 'phone', 'en' => 'Phone']);
    Status::create(['code' => 'pending', 'en' => 'Pending']);
    Wilaya::create(['code' => '16', 'number' => '16', 'en' => 'Algiers']);

    $response = $this->getJson('/api/public/v1/catalogs?includes=professional_specialities,contact_platforms,statuses,wilayas');

    $response->assertOk()
        ->assertJsonCount(1, 'professional_specialities')
        ->assertJsonCount(1, 'contact_platforms')
        ->assertJsonCount(1, 'statuses')
        ->assertJsonCount(1, 'wilayas');
});
