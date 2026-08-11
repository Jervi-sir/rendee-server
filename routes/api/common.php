<?php

use App\Http\Controllers\V1\Api\Auth\DeviceController;
use App\Http\Controllers\V1\Api\Auth\LoginController;
use App\Http\Controllers\V1\Api\Auth\LogoutController;
use App\Http\Controllers\V1\Api\Auth\MeController;
use App\Http\Controllers\V1\Api\Auth\RegisterController;
use App\Http\Controllers\V1\Api\Common\CatalogController;
use App\Http\Controllers\V1\Api\Common\ContactController;
use App\Http\Controllers\V1\Api\Common\NotificationController;

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
});

// ──────────────────────────────────────────────
// Common (public)
// ──────────────────────────────────────────────
Route::get('catalogs', [CatalogController::class, 'index'])->name('api.v1.catalogs');


// ──────────────────────────────────────────────
// Authenticated routes
// ──────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

  // Auth (authenticated)
  Route::prefix('auth')->group(function () {
    Route::post('logout', LogoutController::class)->name('api.v1.auth.logout');
    Route::get('me', MeController::class)->name('api.v1.auth.me');
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
