<?php

use App\Http\Controllers\V1\Api\Partner\AddressController;
use App\Http\Controllers\V1\Api\Partner\BookingController;
use App\Http\Controllers\V1\Api\Partner\CalendarController;
use App\Http\Controllers\V1\Api\Partner\DashboardController;
use App\Http\Controllers\V1\Api\Partner\OnboardingController;
use App\Http\Controllers\V1\Api\Partner\PatientsController;
use App\Http\Controllers\V1\Api\Partner\ProfileController;
use App\Http\Controllers\V1\Api\Partner\ScheduleController;
use App\Http\Controllers\V1\Api\Partner\ServiceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ──────────────────────────────────────────────
// Authenticated routes
// ──────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // ──────────────────────────────────────────
    // Common Calendar (Shared across Professionals and Centers)
    // ──────────────────────────────────────────
    Route::prefix('calendar')->group(function () {
        Route::get('/', [CalendarController::class, 'index'])->name('api.v1.calendar.index');
    });

    // ──────────────────────────────────────────
    // Partner Schedule (Shared across Professionals and Centers)
    // ──────────────────────────────────────────
    Route::prefix('partner')->group(function () {
        Route::get('schedule', [ScheduleController::class, 'show'])->name('api.v1.partner.schedule.show');
        Route::post('schedule/upsert', [ScheduleController::class, 'upsert'])->name('api.v1.partner.schedule.upsert');
    });

    // ──────────────────────────────────────────
    // Partner Profile (Shared across Professionals, Pharmacists, and Centers)
    // ──────────────────────────────────────────
    Route::prefix('partner')->group(function () {
        Route::get('profile', [ProfileController::class, 'show'])->name('api.v1.partner.profile.show');
        Route::get('profile/preview', [ProfileController::class, 'preview'])->name('api.v1.partner.profile.preview');
        Route::match(['put', 'post'], 'profile', [ProfileController::class, 'update'])->name('api.v1.partner.profile.update');
    });

    // ──────────────────────────────────────────
    // Partner Services (Shared across Professionals and Centers)
    // ──────────────────────────────────────────
    Route::prefix('partner')->group(function () {
        Route::get('services', [ServiceController::class, 'index'])->name('api.v1.partner.services.index');
        Route::get('services/catalog', [ServiceController::class, 'catalog'])->name('api.v1.partner.services.catalog');
        Route::post('services', [ServiceController::class, 'store'])->name('api.v1.partner.services.store');
        Route::put('services/{id}', [ServiceController::class, 'update'])->name('api.v1.partner.services.update');
        Route::delete('services/{id}', [ServiceController::class, 'destroy'])->name('api.v1.partner.services.destroy');
    });

    // ──────────────────────────────────────────
    // Professional
    // ──────────────────────────────────────────
    Route::prefix('partner')->name('api.v1.professional.')->group(function () {
        // Dashboard
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Profile
        Route::get('profile', [ProfileController::class, 'show'])->name('profile.show');
        Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');

        // Services
        Route::get('services', [ServiceController::class, 'index'])->name('services.index');
        Route::get('services/catalog', [ServiceController::class, 'catalog'])->name('services.catalog');
        Route::post('services', [ServiceController::class, 'store'])->name('services.store');
        Route::put('services/{id}', [ServiceController::class, 'update'])->name('services.update');
        Route::delete('services/{id}', [ServiceController::class, 'destroy'])->name('services.destroy');

        // Onboarding
        Route::get('onboarding', [OnboardingController::class, 'index'])->name('onboarding.index');
        Route::post('onboarding/complete', [OnboardingController::class, 'complete'])->name('onboarding.complete');
        Route::post('onboarding/step-speciality', [OnboardingController::class, 'stepSpeciality'])->name('onboarding.step-speciality');
        Route::post('onboarding/step-location', [OnboardingController::class, 'stepLocation'])->name('onboarding.step-location');
        Route::post('onboarding/step-services', [OnboardingController::class, 'stepServices'])->name('onboarding.step-services');
        Route::post('onboarding/step-schedule', [OnboardingController::class, 'stepSchedule'])->name('onboarding.step-schedule');
        Route::post('onboarding/step-contacts', [OnboardingController::class, 'stepContacts'])->name('onboarding.step-contacts');

        // Calendar / Agenda
        Route::get('calendar', [CalendarController::class, 'index'])->name('calendar.index');

        // Schedule
        Route::get('schedule', [ScheduleController::class, 'show'])->name('schedule.show');
        Route::post('schedule/upsert', [ScheduleController::class, 'upsert'])->name('schedule.upsert');

        // Address
        Route::get('address', [AddressController::class, 'show'])->name('address.show');
        Route::post('address/upsert', [AddressController::class, 'upsert'])->name('address.upsert');

        // Patients
        Route::get('patients', [PatientsController::class, 'index'])->name('patients.index');
        Route::get('patients/{id}', [PatientsController::class, 'show'])->name('patients.show');
        Route::get('patients/{id}/history', [PatientsController::class, 'history'])->name('patients.history');

        // Bookings
        Route::get('bookings', [BookingController::class, 'index'])->name('bookings.index');
        Route::get('bookings/{id}', [BookingController::class, 'show'])->name('bookings.show');
        Route::put('bookings/{id}', [BookingController::class, 'update'])->name('bookings.update');
        Route::post('bookings/{id}/suggest', [BookingController::class, 'suggest'])->name('bookings.suggest');
    });
});
