<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api\Center\M4;

use App\Http\Controllers\Controller;
use App\Models\Center;
use App\Models\CenterService;
use App\Models\CenterWorkingHour;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CenterProfileOnboardingController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $center = null;

        if ($user) {
            $center = Center::where('user_id', $user->id)->first();
        }

        if (!$center) {
            $center = Center::first();
        }

        if (!$center) {
            return response()->json([
                'missing_items' => [
                    'رقم الترخيص',
                    'وصف المركز',
                    'العنوان',
                    'المدينة',
                    'خدمة واحدة على الأقل',
                    'موعد متاح واحد على الأقل',
                ],
            ]);
        }

        $missingItems = [];

        if (empty($center->license_number)) {
            $missingItems[] = 'رقم الترخيص';
        }
        if (empty($center->description)) {
            $missingItems[] = 'وصف المركز';
        }
        if (empty($center->address)) {
            $missingItems[] = 'العنوان';
        }
        if (empty($center->city)) {
            $missingItems[] = 'المدينة';
        }
        if (!$center->emergency_24_7) {
            $missingItems[] = 'تفعيل خدمة 24/7';
        }
        if (!$center->is_active) {
            $missingItems[] = 'تفعيل الحساب';
        }

        $hasServices = CenterService::where('center_id', $center->id)->where('is_active', true)->exists();
        if (!$hasServices) {
            $missingItems[] = 'خدمة واحدة على الأقل';
        }

        $hasWorkingHours = CenterWorkingHour::where('center_id', $center->id)->where('is_available', true)->exists();
        if (!$hasWorkingHours) {
            $missingItems[] = 'موعد متاح واحد على الأقل';
        }

        return response()->json([
            'missing_items' => $missingItems,
        ]);
    }
}
