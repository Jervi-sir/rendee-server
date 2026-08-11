<?php

use App\Http\Controllers\V1\Api\Auth\DeviceController;
use App\Http\Controllers\V1\Api\Auth\LoginController;
use App\Http\Controllers\V1\Api\Auth\LogoutController;
use App\Http\Controllers\V1\Api\Auth\MeController;
use App\Http\Controllers\V1\Api\Auth\RegisterController;
use App\Http\Controllers\V1\Api\Center\BookingController as CenterBookingController;
use App\Http\Controllers\V1\Api\Center\DashboardController as CenterDashboardController;
use App\Http\Controllers\V1\Api\Center\ProfileController as CenterProfileController;
use App\Http\Controllers\V1\Api\Center\ServiceController as CenterServiceController;
use App\Http\Controllers\V1\Api\Common\CatalogController;
use App\Http\Controllers\V1\Api\Common\NotificationController;
use App\Http\Controllers\V1\Api\Partner\ProfileController;
use App\Http\Controllers\V1\Api\Partner\ServiceController;
use App\Http\Controllers\V1\Api\Patient\ActionsController;
use App\Http\Controllers\V1\Api\Patient\BookingController as PatientBookingController;
use App\Http\Controllers\V1\Api\Patient\CenterController;
use App\Http\Controllers\V1\Api\Patient\FeedController;
use App\Http\Controllers\V1\Api\Patient\MapController;
use App\Http\Controllers\V1\Api\Patient\OnboardingController as PatientOnboardingController;
use App\Http\Controllers\V1\Api\Patient\PharmacistController;
use App\Http\Controllers\V1\Api\Patient\ProfessionalController;
use App\Http\Controllers\V1\Api\Patient\ProfileController as PatientProfileController;
use App\Http\Controllers\V1\Api\Patient\RatingController;
use App\Http\Controllers\V1\Api\Patient\SearchController;
use App\Http\Controllers\V1\Api\Pharmacist\BookingController as PharmacistBookingController;
use App\Http\Controllers\V1\Api\Pharmacist\DashboardController as PharmacistDashboardController;
use App\Http\Controllers\V1\Api\Pharmacist\ProfileController as PharmacistProfileController;
use App\Http\Controllers\V1\Api\Pharmacist\ServiceController as PharmacistServiceController;
use App\Http\Controllers\V1\Api\Professional\AddressController;
use App\Http\Controllers\V1\Api\Partner\BookingController as PartnerBookingController;
use App\Http\Controllers\V1\Api\Partner\CalendarController;
use App\Http\Controllers\V1\Api\Professional\ContactController;
use App\Http\Controllers\V1\Api\Partner\DashboardController as PartnerDashboardController;
use App\Http\Controllers\V1\Api\Professional\OnboardingController;
use App\Http\Controllers\V1\Api\Professional\ProfileController as ProfessionalProfileController;
use App\Http\Controllers\V1\Api\Professional\ScheduleController;
use App\Http\Controllers\V1\Api\Professional\ServiceController as ProfessionalServiceController;
use App\Http\Controllers\V1\Api\Partner\PatientController as PartnerPatientsController;
use App\Http\Controllers\V1\Api\Partner\ProfileController as PartnerProfileController;
use App\Http\Controllers\V1\Api\Partner\ScheduleController as PartnerScheduleController;



use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    require __DIR__ . '/api/common.php';
    require __DIR__ . '/api/patient.php';
    require __DIR__ . '/api/partner.php';
});
