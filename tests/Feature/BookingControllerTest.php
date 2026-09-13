<?php

use App\Models\Booking;
use App\Models\Partner;
use App\Models\PartnerSchedule;
use App\Models\PartnerService;
use App\Models\Patient;
use App\Models\Profession;
use App\Models\Speciality;
use App\Models\Status;
use App\Models\User;
use App\Models\UserRole;
use App\Models\Wilaya;

beforeEach(function () {
    UserRole::firstOrCreate(['code' => 'patient'], ['en' => 'Patient']);
    UserRole::firstOrCreate(['code' => 'doctor'], ['en' => 'Doctor']);
    Status::firstOrCreate(['code' => 'pending'], ['en' => 'Pending']);
    Status::firstOrCreate(['code' => 'confirmed'], ['en' => 'Confirmed']);
    Status::firstOrCreate(['code' => 'cancelled'], ['en' => 'Cancelled']);
});

test('patient can book an appointment successfully with partner_service_id', function () {
    $doctorUser = User::factory()->create(['user_role_code' => 'doctor']);
    $partner = Partner::create([
        'user_id' => $doctorUser->id,
        'name' => 'Dr. Karim Amrani',
        'is_active' => true,
    ]);

    $service = PartnerService::create([
        'partner_id' => $partner->id,
        'name' => 'General Consultation',
        'price' => 2500,
        'duration_minutes' => 30,
    ]);

    $schedule = PartnerSchedule::create([
        'partner_id' => $partner->id,
        'day_of_week' => 0, // Sunday
        'start_time' => '08:00:00',
        'end_time' => '16:00:00',
        'is_active' => true,
    ]);

    $patientUser = User::factory()->create(['user_role_code' => 'patient']);
    $patient = Patient::create(['user_id' => $patientUser->id]);

    $payload = [
        'partner_id' => $partner->id,
        'date' => '2026-09-13', // A Sunday (day_of_week = 0)
        'time' => '09:00',
        'service_id' => $service->id,
        'patient_name' => 'Jervi Doe',
        'patient_phone' => '0555001122',
        'notes' => 'Routine checkup',
    ];

    $response = $this->actingAs($patientUser)
        ->postJson(route('api.v1.patient.bookings.store'), $payload);

    $response->assertCreated()
        ->assertJson([
            'success' => true,
            'message' => 'Appointment booked successfully.',
            'booking' => [
                'partner_id' => $partner->id,
                'patient_id' => $patient->id,
                'patient_name' => 'Jervi Doe',
                'patient_phone' => '0555001122',
                'booking_date' => '2026-09-13',
                'booking_time' => '09:00',
                'status_code' => 'pending',
                'service' => [
                    'id' => $service->id,
                    'name' => 'General Consultation',
                    'price' => 2500,
                ],
            ],
        ]);

    $this->assertDatabaseHas('bookings', [
        'partner_id' => $partner->id,
        'patient_id' => $patient->id,
        'partner_service_id' => $service->id,
        'partner_schedule_id' => $schedule->id,
        'patient_name' => 'Jervi Doe',
        'patient_phone' => '0555001122',
        'booking_date' => '2026-09-13',
        'booking_time' => '09:00',
        'status_code' => 'pending',
    ]);

    $this->assertDatabaseHas('booking_histories', [
        'status_code' => 'pending',
        'notes' => 'Rendez-vous créé.',
        'changed_by' => $patientUser->id,
    ]);
});

test('patient can view their bookings and booking details', function () {
    $doctorUser = User::factory()->create(['user_role_code' => 'doctor']);
    $partner = Partner::create([
        'user_id' => $doctorUser->id,
        'name' => 'Dr. Karim Amrani',
        'is_active' => true,
    ]);

    $service = PartnerService::create([
        'partner_id' => $partner->id,
        'name' => 'General Consultation',
        'price' => 2500,
        'duration_minutes' => 30,
    ]);

    $patientUser = User::factory()->create(['user_role_code' => 'patient']);
    $patient = Patient::create(['user_id' => $patientUser->id]);

    $booking = Booking::create([
        'reference' => 'BK-TEST0001',
        'patient_id' => $patient->id,
        'partner_id' => $partner->id,
        'partner_service_id' => $service->id,
        'patient_name' => 'Jervi Doe',
        'patient_phone' => '0555001122',
        'booking_date' => '2026-09-13',
        'booking_time' => '10:00:00',
        'status_code' => 'pending',
    ]);

    $listResponse = $this->actingAs($patientUser)
        ->getJson(route('api.v1.patient.bookings.index'));

    $listResponse->assertOk()
        ->assertJsonCount(1, 'bookings')
        ->assertJsonPath('bookings.0.id', $booking->id);

    $showResponse = $this->actingAs($patientUser)
        ->getJson(route('api.v1.patient.bookings.show', $booking->id));

    $showResponse->assertOk()
        ->assertJsonPath('booking.id', $booking->id)
        ->assertJsonPath('booking.service.id', $service->id);
});

test('patient can update pending booking date and time', function () {
    $doctorUser = User::factory()->create(['user_role_code' => 'doctor']);
    $partner = Partner::create([
        'user_id' => $doctorUser->id,
        'name' => 'Dr. Karim Amrani',
        'is_active' => true,
    ]);

    $patientUser = User::factory()->create(['user_role_code' => 'patient']);
    $patient = Patient::create(['user_id' => $patientUser->id]);

    $booking = Booking::create([
        'reference' => 'BK-TEST0002',
        'patient_id' => $patient->id,
        'partner_id' => $partner->id,
        'patient_name' => 'Jervi Doe',
        'patient_phone' => '0555001122',
        'booking_date' => '2026-09-13',
        'booking_time' => '10:00:00',
        'status_code' => 'pending',
    ]);

    $response = $this->actingAs($patientUser)
        ->putJson(route('api.v1.patient.bookings.update', $booking->id), [
            'booking_date' => '2026-09-15',
            'booking_time' => '14:00',
            'notes' => 'Changed time',
        ]);

    $response->assertOk()
        ->assertJsonPath('success', true);

    $this->assertDatabaseHas('bookings', [
        'id' => $booking->id,
        'booking_date' => '2026-09-15',
        'booking_time' => '14:00',
        'notes' => 'Changed time',
    ]);
});

test('patient can confirm proposal from partner', function () {
    $doctorUser = User::factory()->create(['user_role_code' => 'doctor']);
    $partner = Partner::create([
        'user_id' => $doctorUser->id,
        'name' => 'Dr. Karim Amrani',
        'is_active' => true,
    ]);

    $patientUser = User::factory()->create(['user_role_code' => 'patient']);
    $patient = Patient::create(['user_id' => $patientUser->id]);

    $booking = Booking::create([
        'reference' => 'BK-TEST0003',
        'patient_id' => $patient->id,
        'partner_id' => $partner->id,
        'patient_name' => 'Jervi Doe',
        'patient_phone' => '0555001122',
        'booking_date' => '2026-09-13',
        'booking_time' => '10:00:00',
        'status_code' => 'pending',
        'has_pending_proposal' => true,
        'proposed_date' => '2026-09-16',
        'proposed_time' => '11:00:00',
    ]);

    $response = $this->actingAs($patientUser)
        ->postJson(route('api.v1.patient.bookings.confirm-proposal', $booking->id));

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('booking.status_code', 'confirmed');

    $this->assertDatabaseHas('bookings', [
        'id' => $booking->id,
        'status_code' => 'confirmed',
        'has_pending_proposal' => 0,
    ]);
    expect($booking->fresh()->booking_date->format('Y-m-d'))->toBe('2026-09-16');
    expect($booking->fresh()->booking_time)->toBe('11:00:00');
});

test('attempt booking returns partner profession, speciality, and location', function () {
    Profession::firstOrCreate(['code' => 'doctor'], ['en' => 'Doctor', 'hex' => '#000000']);
    Speciality::firstOrCreate(['code' => 'cardiology'], ['en' => 'Cardiology', 'profession_code' => 'doctor']);
    Wilaya::firstOrCreate(['code' => '16'], ['number' => '16', 'en' => 'Algiers', 'ar' => 'الجزائر']);

    $doctorUser = User::factory()->create(['user_role_code' => 'doctor']);
    $partner = Partner::create([
        'user_id' => $doctorUser->id,
        'name' => 'Dr. Karim Amrani',
        'profession_code' => 'doctor',
        'speciality_code' => 'cardiology',
        'wilaya_code' => '16',
        'city' => 'Hydra',
        'address' => '12 Rue des Pins',
        'is_active' => true,
    ]);

    $patientUser = User::factory()->create(['user_role_code' => 'patient']);

    $response = $this->actingAs($patientUser)
        ->getJson(route('api.v1.patient.bookings.attempt', ['partner_id' => $partner->id]));

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'bookable' => [
                'id' => $partner->id,
                'name' => 'Dr. Karim Amrani',
                'profession_code' => 'doctor',
                'speciality_code' => 'cardiology',
                'profession' => 'Doctor',
                'speciality' => 'Cardiology',
                'specialty' => 'Cardiology',
                'wilaya_code' => '16',
                'city' => 'Hydra',
                'address' => '12 Rue des Pins',
            ],
        ]);
});
