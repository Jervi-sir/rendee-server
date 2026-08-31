<?php

use App\Http\Controllers\V1\Api\Auth\ChangePasswordController;
use App\Http\Controllers\V1\Api\Auth\DeviceController;
use App\Http\Controllers\V1\Api\Auth\ForgotPasswordController;
use App\Http\Controllers\V1\Api\Auth\LoginController;
use App\Http\Controllers\V1\Api\Auth\LogoutController;
use App\Http\Controllers\V1\Api\Auth\MeController;
use App\Http\Controllers\V1\Api\Auth\RegisterController;
use App\Http\Controllers\V1\Api\Common\CatalogController;
use App\Http\Controllers\V1\Api\Common\ContactController;
use App\Http\Controllers\V1\Api\Common\NotificationController;
use App\Http\Controllers\V1\Api\Common\SupportMessageController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ──────────────────────────────────────────────
// Auth (public)
// ──────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('login', [LoginController::class, 'store'])->name('api.v1.auth.login');
    Route::post('register', [RegisterController::class, 'store'])->name('api.v1.auth.register');

    // Forgot Password
    Route::post('forgot-password/send-otp', [ForgotPasswordController::class, 'sendOtp'])->name('api.v1.auth.forgot-password.send-otp');
    Route::post('forgot-password/verify-otp', [ForgotPasswordController::class, 'verifyOtp'])->name('api.v1.auth.forgot-password.verify-otp');
    Route::post('forgot-password/reset', [ForgotPasswordController::class, 'resetPassword'])->name('api.v1.auth.forgot-password.reset');
});

// ──────────────────────────────────────────────
// Common (public / optional auth)
// ──────────────────────────────────────────────
Route::get('catalogs', [CatalogController::class, 'index'])->name('api.v1.catalogs');
Route::post('support-messages', [SupportMessageController::class, 'store'])->name('api.v1.support-messages.store');
Route::post('notifications/test', [NotificationController::class, 'sendTest'])->name('api.v1.notifications.public-test');

// ──────────────────────────────────────────────
// Authenticated routes
// ──────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Auth (authenticated)
    Route::prefix('auth')->group(function () {
        Route::post('logout', LogoutController::class)->name('api.v1.auth.logout');
        Route::get('me', MeController::class)->name('api.v1.auth.me');
        Route::post('change-password', ChangePasswordController::class)->name('api.v1.auth.change-password');
        Route::post('devices', [DeviceController::class, 'store'])->name('api.v1.auth.devices.store');
    });

    // ──────────────────────────────────────────
    // Notifications
    // ──────────────────────────────────────────
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('api.v1.notifications.index');
        Route::put('{id}/read', [NotificationController::class, 'read'])->name('api.v1.notifications.read');
        Route::put('read-all', [NotificationController::class, 'readAll'])->name('api.v1.notifications.read-all');
    });

    // ──────────────────────────────────────────
    // Common Contacts (Shared across all user roles)
    // ──────────────────────────────────────────
    Route::prefix('contacts')->group(function () {
        Route::get('/', [ContactController::class, 'index'])->name('api.v1.contacts.index');
        Route::get('{id}', [ContactController::class, 'show'])->name('api.v1.contacts.show');
        Route::post('upsert', [ContactController::class, 'upsert'])->name('api.v1.contacts.upsert');
        Route::delete('{id}', [ContactController::class, 'destroy'])->name('api.v1.contacts.destroy');
    });
});
