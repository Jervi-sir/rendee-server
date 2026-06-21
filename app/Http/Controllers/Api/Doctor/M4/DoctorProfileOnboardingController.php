<?php

namespace App\Http\Controllers\Api\Doctor\M4;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DoctorProfileOnboardingController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $doctor = null;

        if ($user) {
            $doctor = Doctor::with(['specialty'])->where('user_id', $user->id)->first();
        }

        if (!$doctor) {
            $doctor = Doctor::with(['specialty'])->first();
        }

        if (!$doctor) {
            return response()->json([
                'doctor' => [
                    'id' => null,
                    'specialty_id' => null,
                    'license_number' => null,
                    'years_experience' => null,
                    'bio' => null,
                    'address' => null,
                    'city' => null,
                    'is_available' => false,
                    'schedules' => [],
                ],
                'missing_items' => ['التخصص', 'رقم الترخيص', 'سنوات الخبرة', 'النبذة', 'العنوان', 'المدينة', 'تفعيل التوفر', 'جدول أسبوعي واحد على الأقل'],
            ]);
        }

        $schedules = DoctorSchedule::where('doctor_id', $doctor->id)
            ->where('is_active', true)
            ->get();

        $missing = [];
        if (empty($doctor->speciality_code)) $missing[] = "التخصص";
        if (empty($doctor->license_number)) $missing[] = "رقم الترخيص";
        if (empty($doctor->years_experience)) $missing[] = "سنوات الخبرة";
        if (empty($doctor->bio)) $missing[] = "النبذة";
        if (empty($doctor->address)) $missing[] = "العنوان";
        if (empty($doctor->city)) $missing[] = "المدينة";
        if (!$doctor->is_available) $missing[] = "تفعيل التوفر";
        if ($schedules->isEmpty()) $missing[] = "جدول أسبوعي واحد على الأقل";

        return response()->json([
            'doctor' => [
                'id' => $doctor->id,
                'specialty_id' => $doctor->specialty?->id,
                'license_number' => $doctor->license_number,
                'years_experience' => $doctor->years_experience,
                'bio' => $doctor->bio,
                'address' => $doctor->address,
                'city' => $doctor->city,
                'is_available' => (bool)$doctor->is_available,
                'schedules' => $schedules->toArray(),
            ],
            'missing_items' => $missing,
        ]);
    }
}
