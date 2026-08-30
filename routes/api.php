<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::get('/health', function () {
    return response()->json([
        'status' => 'alive',
        'app' => config('app.name', 'Rendee'),
        'timestamp' => now()->toIso8601String(),
    ]);
})->name('api.health');

Route::prefix('v1')->group(function () {
    Route::get('/health', function () {
        return response()->json([
            'status' => 'alive',
            'version' => 'v1',
            'app' => config('app.name', 'Rendee'),
            'timestamp' => now()->toIso8601String(),
        ]);
    })->name('api.v1.health');

    require __DIR__.'/api/common.php';
    require __DIR__.'/api/patient.php';
    require __DIR__.'/api/partner.php';
});
