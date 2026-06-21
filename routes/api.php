<?php

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\MeController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Patient\M1\M1PatientController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Patient\M2\M2PatientController;
use App\Http\Controllers\Api\Patient\M3\M3PatientController;
use App\Http\Controllers\Api\Patient\M4\M4PatientController;
use App\Http\Controllers\Api\Patient\Center\PatientCenterController;
use App\Http\Controllers\Api\Patient\Doctor\PatientDoctorController;
use App\Http\Controllers\Api\Patient\Nearby\PatientNearbyController;
use App\Http\Controllers\Api\Patient\Onboarding\PatientOnboardingController;
use App\Http\Controllers\Api\Patient\Search\PatientSearchController;

use App\Http\Controllers\Api\Patient\Appointment\AppointmentSubmitController;
use App\Http\Controllers\Api\Patient\Appointment\AppointmentConfirmController;


Route::prefix('public')->middleware(\App\Http\Middleware\OptionalSanctumAuth::class)->group(function (): void {
    Route::get('m1/list', [M1PatientController::class, 'index']);
    Route::get('m2/list', [M2PatientController::class, 'index']);
    Route::get('m3/list', [M3PatientController::class, 'index']);
    Route::get('m4/list', [M4PatientController::class, 'index']);
    Route::get('centers/{id}', [PatientCenterController::class, 'show']);
    Route::get('doctors/{id}', [PatientDoctorController::class, 'show']);
    Route::get('nearby/list', [PatientNearbyController::class, 'index']);
    Route::get('search', [PatientSearchController::class, 'index']);
});

Route::prefix('auth')->group(function (): void {
    Route::post('register', [RegisterController::class, 'store']);
    Route::post('login', [LoginController::class, 'store']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('me', MeController::class);
        Route::post('logout', LogoutController::class);
    });
});

// Doctor Routes
Route::middleware('auth:sanctum')->prefix('doctor')->group(function (): void {
    Route::get('home', [\App\Http\Controllers\Api\Doctor\M1\M1DoctorController::class, 'index']);
    Route::get('appointments', [\App\Http\Controllers\Api\Doctor\M2\M2DoctorController::class, 'index']);
    Route::patch('appointments/{id}', [\App\Http\Controllers\Api\Doctor\M2\M2DoctorController::class, 'update']);
    Route::post('appointments/{id}/suggest-items', [\App\Http\Controllers\Api\Doctor\M2\M2DoctorController::class, 'suggest']);

    // Patients Directory
    Route::get('patients', [\App\Http\Controllers\Api\Doctor\M3\M3DoctorController::class, 'index']);
    Route::get('patients/{id}', [\App\Http\Controllers\Api\Doctor\M3\DoctorPatientDetailsController::class, 'show']);

    // Profile & Settings
    Route::get('profile', [\App\Http\Controllers\Api\Doctor\M4\DoctorPersonalDataController::class, 'show']);
    Route::post('profile', [\App\Http\Controllers\Api\Doctor\M4\DoctorPersonalDataController::class, 'update']);
    Route::get('profile/onboarding', [\App\Http\Controllers\Api\Doctor\M4\DoctorProfileOnboardingController::class, 'show']);
    Route::get('profile/schedule', [\App\Http\Controllers\Api\Doctor\M4\DoctorWeeklyScheduleController::class, 'show']);
    Route::post('profile/schedule', [\App\Http\Controllers\Api\Doctor\M4\DoctorWeeklyScheduleController::class, 'update']);
});

// Center Routes
Route::middleware('auth:sanctum')->prefix('center')->group(function (): void {
    Route::get('home', [\App\Http\Controllers\Api\Center\M1\M1CenterController::class, 'index']);
    Route::get('appointments', [\App\Http\Controllers\Api\Center\M2\M2CenterController::class, 'index']);
    Route::patch('appointments/{id}', [\App\Http\Controllers\Api\Center\M2\M2CenterController::class, 'update']);
    Route::post('appointments/{id}/suggest-items', [\App\Http\Controllers\Api\Center\M2\M2CenterController::class, 'suggest']);

    // Services
    Route::get('services', [\App\Http\Controllers\Api\Center\M3\CenterServiceFormController::class, 'index']);
    Route::get('services/catalog', [\App\Http\Controllers\Api\Center\M3\CenterServiceFormController::class, 'catalog']);
    Route::post('services', [\App\Http\Controllers\Api\Center\M3\CenterServiceFormController::class, 'store']);
    Route::get('services/{id}', [\App\Http\Controllers\Api\Center\M3\CenterServiceFormController::class, 'show']);
    Route::post('services/{id}', [\App\Http\Controllers\Api\Center\M3\CenterServiceFormController::class, 'update']);
    Route::delete('services/{id}', [\App\Http\Controllers\Api\Center\M3\CenterServiceFormController::class, 'destroy']);

    // Profile & Settings
    Route::get('profile', [\App\Http\Controllers\Api\Center\M4\M4CenterController::class, 'index']);
    Route::get('profile/basic', [\App\Http\Controllers\Api\Center\M4\CenterBasicDataController::class, 'show']);
    Route::post('profile/basic', [\App\Http\Controllers\Api\Center\M4\CenterBasicDataController::class, 'update']);
    Route::get('profile/location', [\App\Http\Controllers\Api\Center\M4\CenterLocationController::class, 'show']);
    Route::post('profile/location', [\App\Http\Controllers\Api\Center\M4\CenterLocationController::class, 'update']);
    Route::get('profile/schedule', [\App\Http\Controllers\Api\Center\M4\CenterWorkingHoursController::class, 'show']);
    Route::post('profile/schedule', [\App\Http\Controllers\Api\Center\M4\CenterWorkingHoursController::class, 'update']);
    Route::get('profile/onboarding', [\App\Http\Controllers\Api\Center\M4\CenterProfileOnboardingController::class, 'show']);
});



Route::prefix('public')->middleware('auth:sanctum')->group(function (): void {
    Route::get('patient/onboarding', [PatientOnboardingController::class, 'show']);
    Route::post('patient/onboarding', [PatientOnboardingController::class, 'update']);
    // Appointment Bookings
    Route::get('bookings/{id}/options', [AppointmentSubmitController::class, 'options']);
    Route::post('bookings', [AppointmentSubmitController::class, 'store']);
    Route::get('bookings/{id}', [AppointmentConfirmController::class, 'show']);
    Route::post('bookings/{id}/accept-proposal', [AppointmentConfirmController::class, 'acceptProposal']);
    Route::post('bookings/{id}/reject-proposal', [AppointmentConfirmController::class, 'rejectProposal']);
});
