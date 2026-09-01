<?php

use App\Http\Controllers\V1\Api\Patient\ActionsController;
use App\Http\Controllers\V1\Api\Patient\BookingController;
use App\Http\Controllers\V1\Api\Patient\FeedController;
use App\Http\Controllers\V1\Api\Patient\MapController;
use App\Http\Controllers\V1\Api\Patient\OnboardingController;
use App\Http\Controllers\V1\Api\Patient\PartnerController;
use App\Http\Controllers\V1\Api\Patient\ProfileController;
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

    // Partners
    Route::get('partners/{id}', [PartnerController::class, 'show'])->name('partners.show');
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
        Route::get('profile', [ProfileController::class, 'show'])->name('profile.show');
        Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::get('onboarding', [OnboardingController::class, 'show'])->name('onboarding.show');
        Route::post('onboarding', [OnboardingController::class, 'update'])->name('onboarding.update');

        // Bookings
        Route::get('bookings/attempt', [BookingController::class, 'attemptBooking'])->name('bookings.attempt');
        Route::get('bookings', [BookingController::class, 'index'])->name('bookings.index');
        Route::get('bookings/{id}', [BookingController::class, 'show'])->name('bookings.show');
        Route::post('bookings', [BookingController::class, 'store'])->name('bookings.store');
        Route::put('bookings/{id}', [BookingController::class, 'update'])->name('bookings.update');
        Route::patch('bookings/{id}/reschedule', [BookingController::class, 'update'])->name('bookings.reschedule');
        Route::post('bookings/{id}/confirm-proposal', [BookingController::class, 'confirmProposal'])->name('bookings.confirm-proposal');

        // Actions (Toggle Likes)
        Route::post('toggle-like', [ActionsController::class, 'toggleLike'])->name('toggle-like');
        Route::post('like/toggle', [ActionsController::class, 'toggleLike'])->name('like.toggle');
    });
});
