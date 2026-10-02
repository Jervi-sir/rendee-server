<?php

use App\Models\Booking;
use App\Models\Partner;
use App\Models\PartnerService;
use App\Models\Patient;
use App\Models\Status;
use App\Models\User;
use App\Models\UserRole;

beforeEach(function () {
    UserRole::firstOrCreate(['code' => 'doctor'], ['en' => 'Doctor']);
    UserRole::firstOrCreate(['code' => 'patient'], ['en' => 'Patient']);
    Status::firstOrCreate(['code' => 'pending'], ['en' => 'Pending']);
    Status::firstOrCreate(['code' => 'confirmed'], ['en' => 'Confirmed']);
    Status::firstOrCreate(['code' => 'completed'], ['en' => 'Completed']);
    Status::firstOrCreate(['code' => 'cancelled'], ['en' => 'Cancelled']);
});

test('partner can complete, cancel, suggest new time, and create follow-up booking', function () {
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
        'reference' => 'BK-TEST1001',
        'patient_id' => $patient->id,
        'partner_id' => $partner->id,
        'partner_service_id' => $service->id,
        'patient_name' => 'John Doe',
        'patient_phone' => '0555112233',
        'booking_date' => '2026-10-05',
        'booking_time' => '10:00:00',
        'status_code' => 'confirmed',
    ]);

    // 1. Suggest alternative time for confirmed appointment
    $suggestRes = $this->actingAs($doctorUser)
        ->postJson(route('api.v1.professional.bookings.suggest', $booking->id), [
            'proposed_date' => '2026-10-06',
            'proposed_time' => '14:00',
            'notes' => 'Dr is delayed',
        ]);
    $suggestRes->assertOk()
        ->assertJsonPath('success', true);

    expect($booking->fresh()->proposed_date->format('Y-m-d'))->toBe('2026-10-06');
    expect($booking->fresh()->has_pending_proposal)->toBeTrue();

    // 2. Complete appointment
    $completeRes = $this->actingAs($doctorUser)
        ->postJson(route('api.v1.professional.bookings.complete', $booking->id), [
            'notes' => 'Exam finished successfully',
        ]);
    $completeRes->assertOk()
        ->assertJsonPath('success', true);

    expect($booking->fresh()->status_code)->toBe('completed');
    expect($booking->fresh()->has_pending_proposal)->toBeFalse();

    // 3. Create follow-up booking
    $followUpRes = $this->actingAs($doctorUser)
        ->postJson(route('api.v1.professional.bookings.follow-up', $booking->id), [
            'booking_date' => '2026-10-20',
            'booking_time' => '11:30',
            'notes' => 'Follow up on lab tests',
        ]);

    $followUpRes->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('booking.patient_id', $patient->id)
        ->assertJsonPath('booking.partner_id', $partner->id)
        ->assertJsonPath('booking.booking_date', '2026-10-20')
        ->assertJsonPath('booking.status_code', 'confirmed');

    // 4. Cancel appointment
    $cancelRes = $this->actingAs($doctorUser)
        ->postJson(route('api.v1.professional.bookings.cancel', $booking->id), [
            'reason' => 'Patient requested cancellation',
        ]);

    $cancelRes->assertOk()
        ->assertJsonPath('success', true);

    expect($booking->fresh()->status_code)->toBe('cancelled');
});
