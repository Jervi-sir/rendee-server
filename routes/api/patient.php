<?php

use App\Http\Controllers\V1\Api\Patient\ActionsController;
use App\Http\Controllers\V1\Api\Patient\BookingController as PatientBookingController;
use App\Http\Controllers\V1\Api\Patient\CenterController;
use App\Http\Controllers\V1\Api\Patient\FeedController;
use App\Http\Controllers\V1\Api\Patient\MapController;
use App\Http\Controllers\V1\Api\Patient\OnboardingController as PatientOnboardingController;
use App\Http\Controllers\V1\Api\Patient\PartnerController;
use App\Http\Controllers\V1\Api\Patient\PharmacistController;
use App\Http\Controllers\V1\Api\Patient\ProfessionalController;
use App\Http\Controllers\V1\Api\Patient\ProfileController as PatientProfileController;
use App\Http\Controllers\V1\Api\Patient\RatingController;
use App\Http\Controllers\V1\Api\Patient\SearchController;

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/


// ──────────────────────────────────────────────
// Patient (Public / Optional Auth)
// ──────────────────────────────────────────────
Route::prefix('patient')->name('api.v1.patient.')->group(function () {
  // Feed
  Route::get('feed', [FeedController::class, 'index'])->name('feed');

  // Map
  Route::get('map', [MapController::class, 'index'])->name('map');

  // Search
  Route::get('search', [SearchController::class, 'index'])->name('search');
  Route::get('search/feed', [SearchController::class, 'feed'])->name('search.feed');

  // Partners
  Route::get('partners/{id}', [PartnerController::class, 'show'])->name('partners.show');

  // Professionals
  Route::get('professionals', [ProfessionalController::class, 'index'])->name('professionals.index');
  Route::get('professionals/{id}', [ProfessionalController::class, 'show'])->name('professionals.show');

  // Centers
  Route::get('centers', [CenterController::class, 'index'])->name('centers.index');
  Route::get('centers/{id}', [CenterController::class, 'show'])->name('centers.show');

  // Pharmacies
  Route::get('pharmacies', [PharmacistController::class, 'index'])->name('pharmacies.index');
  Route::get('pharmacies/{id}', [PharmacistController::class, 'show'])->name('pharmacies.show');
});

// ──────────────────────────────────────────────
// Authenticated routes
// ──────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {
  // ──────────────────────────────────────────
  // Patient (Protected)
  // ──────────────────────────────────────────
  Route::prefix('patient')->name('api.v1.patient.')->group(function () {
    // Profile & Onboarding
    Route::get('profile', [PatientProfileController::class, 'show'])->name('profile.show');
    Route::put('profile', [PatientProfileController::class, 'update'])->name('profile.update');
    Route::get('onboarding', [PatientOnboardingController::class, 'show'])->name('onboarding.show');
    Route::post('onboarding', [PatientOnboardingController::class, 'update'])->name('onboarding.update');

    // Bookings
    Route::get('bookings/attempt', [PatientBookingController::class, 'attemptBooking'])->name('bookings.attempt');
    Route::get('bookings', [PatientBookingController::class, 'index'])->name('bookings.index');
    Route::get('bookings/{id}', [PatientBookingController::class, 'show'])->name('bookings.show');
    Route::post('bookings', [PatientBookingController::class, 'store'])->name('bookings.store');
    Route::post('bookings/{id}/confirm-proposal', [PatientBookingController::class, 'confirmProposal'])->name('bookings.confirm-proposal');

    // Ratings
    Route::post('ratings', [RatingController::class, 'store'])->name('ratings.store');

    // Actions (Toggle Likes)
    Route::post('toggle-like', [ActionsController::class, 'toggleLike'])->name('toggle-like');
    Route::post('like/toggle', [ActionsController::class, 'toggleLike'])->name('like.toggle');
  });
});
