<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    require __DIR__.'/api/common.php';
    require __DIR__.'/api/patient.php';
    require __DIR__.'/api/partner.php';
});
