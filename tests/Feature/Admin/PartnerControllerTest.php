<?php

use App\Models\Partner;
use App\Models\PartnerType;
use App\Models\User;

test('authenticated user can view partners list page', function () {
    $user = User::factory()->create();
    $partnerUser = User::factory()->create(['name' => 'Dr. Ahmed', 'email' => 'ahmed@test.com']);
    $partnerType = PartnerType::create(['code' => 'doctor', 'en' => 'Doctor', 'fr' => 'Doc', 'ar' => 'طبيب']);

    Partner::create([
        'user_id' => $partnerUser->id,
        'partner_type_code' => 'doctor',
        'name' => 'Dr. Ahmed Clinic',
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)
        ->get(route('admin.partners.index'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/partners/list')
            ->has('partners.data', 1)
            ->has('partnerTypes', 1)
        );
});

test('can filter partners by search and status', function () {
    $user = User::factory()->create();
    $u1 = User::factory()->create();
    $u2 = User::factory()->create();

    $p1 = Partner::create([
        'user_id' => $u1->id,
        'name' => 'Cardio Care Clinic',
        'is_active' => true,
    ]);

    $p2 = Partner::create([
        'user_id' => $u2->id,
        'name' => 'Oran Dental Studio',
        'is_active' => false,
    ]);

    // Search filter
    $searchRes = $this->actingAs($user)
        ->get(route('admin.partners.index', ['search' => 'Cardio']));

    $searchRes->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('partners.data', 1)
            ->where('partners.data.0.id', $p1->id)
        );

    // Status filter
    $statusRes = $this->actingAs($user)
        ->get(route('admin.partners.index', ['status' => 'inactive']));

    $statusRes->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('partners.data', 1)
            ->where('partners.data.0.id', $p2->id)
        );
});

test('can toggle partner approval status', function () {
    $user = User::factory()->create();
    $partnerUser = User::factory()->create();

    $partner = Partner::create([
        'user_id' => $partnerUser->id,
        'name' => 'Test Partner',
        'is_active' => false,
    ]);

    $response = $this->actingAs($user)
        ->patchJson(route('admin.partners.toggle-status', $partner->id));

    $response->assertOk()
        ->assertJsonFragment(['is_active' => true]);

    $this->assertDatabaseHas('partners', [
        'id' => $partner->id,
        'is_active' => true,
    ]);
});

test('can view and update partner details', function () {
    $user = User::factory()->create();
    $partnerUser = User::factory()->create();
    $partnerType = PartnerType::create(['code' => 'doctor', 'en' => 'Doctor', 'fr' => 'Doc', 'ar' => 'طبيب']);

    $partner = Partner::create([
        'user_id' => $partnerUser->id,
        'partner_type_code' => 'doctor',
        'name' => 'Initial Name',
        'license_number' => 'INIT-123',
    ]);

    // View show page
    $showRes = $this->actingAs($user)
        ->get(route('admin.partners.show', $partner->id));

    $showRes->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/partners/show')
            ->where('partner.id', $partner->id)
        );

    // Update
    $updateRes = $this->actingAs($user)
        ->putJson(route('admin.partners.update', $partner->id), [
            'name' => 'Updated Name Clinic',
            'partner_type_code' => 'doctor',
            'license_number' => 'NEW-999',
            'is_active' => true,
            'is_available' => true,
        ]);

    $updateRes->assertOk();

    $this->assertDatabaseHas('partners', [
        'id' => $partner->id,
        'name' => 'Updated Name Clinic',
        'license_number' => 'NEW-999',
    ]);
});
