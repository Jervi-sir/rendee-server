<?php

use App\Http\Controllers\Admin\Catalogs\ProfessionController;
use App\Http\Controllers\Admin\Partners\PartnerController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::prefix('catalogs')->name('catalogs.')->group(function () {
            Route::get('professions', [ProfessionController::class, 'index'])->name('professions.index');
            Route::post('professions', [ProfessionController::class, 'store'])->name('professions.store');
            Route::put('professions/{profession}', [ProfessionController::class, 'update'])->name('professions.update');
            Route::delete('professions/{profession}', [ProfessionController::class, 'destroy'])->name('professions.destroy');
            Route::get('professions/{profession}/specialities', [ProfessionController::class, 'specialities'])->name('professions.specialities');
            Route::post('professions/{profession}/specialities', [ProfessionController::class, 'storeSpeciality'])->name('professions.specialities.store');
            Route::delete('specialities/{speciality}', [ProfessionController::class, 'destroySpeciality'])->name('specialities.destroy');
        });

        Route::prefix('partners')->name('partners.')->group(function () {
            Route::get('/', [PartnerController::class, 'index'])->name('index');
            Route::get('{partner}', [PartnerController::class, 'show'])->name('show');
            Route::put('{partner}', [PartnerController::class, 'update'])->name('update');
            Route::patch('{partner}/toggle-status', [PartnerController::class, 'toggleStatus'])->name('toggle-status');
            Route::delete('{partner}', [PartnerController::class, 'destroy'])->name('destroy');
        });
    });
});

require __DIR__.'/settings.php';
