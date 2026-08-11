<?php

namespace App\Http\Controllers\V1\Api\Partner;

use App\Http\Controllers\Controller;
use App\Models\Professional;
use App\Models\ProfessionalSchedule;
use App\Models\ProfessionalService;
use App\Models\ProfessionalSpeciality;
use App\Models\ServiceCatalog;
use App\Models\User;
use App\Models\UserContact;
use App\Models\Wilaya;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class OnboardingController extends Controller
{
    /**
     * Get onboarding status, current step, and step completion indicators.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $professional = null;

        if ($user) {
            $professional = Professional::with(['specialty', 'wilaya', 'services', 'schedules', 'contacts'])
                ->where('user_id', $user->id)
                ->first();
        }

        if (! $professional) {
            $professional = Professional::with(['specialty', 'wilaya', 'services', 'schedules', 'contacts'])->first();
            if ($professional) {
                $user = User::find($professional->user_id);
            }
        }

        if (! $professional || ! $user) {
            return response()->json([
                'success' => false,
                'error' => 'Professional profile not found',
            ], 404);
        }

        $stepStatus = $this->calculateStepStatus($user, $professional);
        $isComplete = $stepStatus['is_completed'];

        // Update profile_complete flag if changed
        if ($user->profile_complete !== $isComplete) {
            $user->profile_complete = $isComplete;
            $user->save();
        }

        return response()->json([
            'success' => true,
            'is_completed' => $isComplete,
            'current_step' => $stepStatus['current_step'],
            'total_steps' => 5,
            'steps' => $stepStatus['steps'],
            'data' => [
                'speciality_code' => $professional->professional_speciality_code,
                'speciality_name' => $professional->specialty?->ar ?? $professional->specialty?->en ?? null,
                'license_number' => $professional->license_number,
                'years_experience' => $professional->years_experience,
                'bio' => $professional->bio,
                'wilaya_code' => $professional->wilaya_code,
                'wilaya_name' => $professional->wilaya?->ar ?? $professional->wilaya?->en ?? null,
                'city' => $professional->city,
                'address' => $professional->address,
                'latitude' => $professional->latitude ? (float) $professional->latitude : null,
                'longitude' => $professional->longitude ? (float) $professional->longitude : null,
                'services_count' => $professional->services->count(),
                'schedules_count' => $professional->schedules->where('is_active', true)->count(),
                'contacts_count' => $professional->contacts->count(),
            ],
        ]);
    }

    /**
     * Step 1: Upsert Specialty and Professional Information.
     */
    public function stepSpeciality(Request $request): JsonResponse
    {
        [$user, $professional] = $this->resolveUserAndProfessional($request);
        if (! $professional || ! $user) {
            return response()->json(['error' => 'Professional profile not found'], 404);
        }

        $validated = $request->validate([
            'professional_speciality_code' => ['nullable', 'string', Rule::exists('professional_specialities', 'code')],
            'specialty_id' => ['nullable', 'integer'],
            'license_number' => ['nullable', 'string', 'max:100'],
            'years_experience' => ['nullable', 'string', 'max:10'],
            'bio' => ['nullable', 'string'],
        ]);

        $specialityCode = $validated['professional_speciality_code'] ?? null;
        if (! $specialityCode && ! empty($validated['specialty_id'])) {
            $speciality = ProfessionalSpeciality::find($validated['specialty_id']);
            if ($speciality) {
                $specialityCode = $speciality->code;
            }
        }

        if ($specialityCode) {
            $professional->professional_speciality_code = $specialityCode;
        }
        if (array_key_exists('license_number', $validated)) {
            $professional->license_number = $validated['license_number'];
        }
        if (array_key_exists('years_experience', $validated)) {
            $professional->years_experience = $validated['years_experience'];
        }
        if (array_key_exists('bio', $validated)) {
            $professional->bio = $validated['bio'];
        }
        $professional->save();

        return $this->buildOnboardingResponse($user, $professional, 'Speciality details saved successfully.');
    }

    /**
     * Step 2: Upsert Location & Wilaya.
     */
    public function stepLocation(Request $request): JsonResponse
    {
        [$user, $professional] = $this->resolveUserAndProfessional($request);
        if (! $professional || ! $user) {
            return response()->json(['error' => 'Professional profile not found'], 404);
        }

        $validated = $request->validate([
            'wilaya_code' => ['required', 'string', Rule::exists('wilayas', 'code')],
            'city' => ['required', 'string', 'max:100'],
            'address' => ['required', 'string', 'max:500'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
        ]);

        $professional->wilaya_code = $validated['wilaya_code'];
        $professional->city = $validated['city'];
        $professional->address = $validated['address'];
        if (array_key_exists('latitude', $validated)) {
            $professional->latitude = $validated['latitude'];
        }
        if (array_key_exists('longitude', $validated)) {
            $professional->longitude = $validated['longitude'];
        }
        $professional->save();

        return $this->buildOnboardingResponse($user, $professional, 'Location details saved successfully.');
    }

    /**
     * Step 3: Upsert Services.
     */
    public function stepServices(Request $request): JsonResponse
    {
        [$user, $professional] = $this->resolveUserAndProfessional($request);
        if (! $professional || ! $user) {
            return response()->json(['error' => 'Professional profile not found'], 404);
        }

        $validated = $request->validate([
            'services' => ['required', 'array', 'min:1'],
            'services.*.id' => ['nullable', 'integer'],
            'services.*.name' => ['nullable', 'string', 'max:255'],
            'services.*.service_catalog_code' => ['nullable', 'string'],
            'services.*.price' => ['required', 'numeric', 'min:0'],
            'services.*.duration_minutes' => ['nullable', 'integer', 'min:5'],
        ]);

        foreach ($validated['services'] as $svcItem) {
            $catalogCode = $svcItem['service_catalog_code'] ?? null;
            $name = $svcItem['name'] ?? null;

            if (! $catalogCode && $name) {
                $baseCode = 'pro_' . Str::slug($name, '_');
                $catalog = ServiceCatalog::firstOrCreate(
                    ['code' => $baseCode],
                    ['source' => 'professional', 'ar' => $name, 'en' => $name]
                );
                $catalogCode = $catalog->code;
            }

            if ($catalogCode) {
                ProfessionalService::updateOrCreate(
                    [
                        'professional_id' => $professional->id,
                        'service_catalog_code' => $catalogCode,
                    ],
                    [
                        'price' => $svcItem['price'],
                        'duration_minutes' => $svcItem['duration_minutes'] ?? 30,
                    ]
                );
            }
        }

        return $this->buildOnboardingResponse($user, $professional, 'Services saved successfully.');
    }

    /**
     * Step 4: Upsert Schedule.
     */
    public function stepSchedule(Request $request): JsonResponse
    {
        [$user, $professional] = $this->resolveUserAndProfessional($request);
        if (! $professional || ! $user) {
            return response()->json(['error' => 'Professional profile not found'], 404);
        }

        $validated = $request->validate([
            'schedules' => ['required', 'array', 'min:1'],
            'schedules.*.day_of_week' => ['required', 'integer', 'between:0,6'],
            'schedules.*.start_time' => ['nullable', 'string'],
            'schedules.*.end_time' => ['nullable', 'string'],
            'schedules.*.is_active' => ['required', 'boolean'],
        ]);

        foreach ($validated['schedules'] as $sch) {
            ProfessionalSchedule::updateOrCreate(
                [
                    'professional_id' => $professional->id,
                    'day_of_week' => (int) $sch['day_of_week'],
                ],
                [
                    'start_time' => $sch['start_time'] ?? '08:00',
                    'end_time' => $sch['end_time'] ?? '17:00',
                    'is_active' => (bool) $sch['is_active'],
                ]
            );
        }

        return $this->buildOnboardingResponse($user, $professional, 'Schedule saved successfully.');
    }

    /**
     * Step 5: Upsert Contacts.
     */
    public function stepContacts(Request $request): JsonResponse
    {
        [$user, $professional] = $this->resolveUserAndProfessional($request);
        if (! $professional || ! $user) {
            return response()->json(['error' => 'Professional profile not found'], 404);
        }

        $validated = $request->validate([
            'phone_public' => ['nullable', 'string', 'max:30'],
            'contacts' => ['nullable', 'array'],
            'contacts.*.platform_code' => ['required', 'string', Rule::exists('contact_platforms', 'code')],
            'contacts.*.url' => ['required', 'string', 'max:500'],
        ]);

        if (! empty($validated['phone_public'])) {
            $professional->phone_public = $validated['phone_public'];
            $professional->save();
        }

        if (! empty($validated['contacts'])) {
            foreach ($validated['contacts'] as $c) {
                UserContact::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'platform_code' => $c['platform_code'],
                    ],
                    [
                        'url' => $c['url'],
                        'target_user_type' => 'professional',
                    ]
                );
            }
        }

        return $this->buildOnboardingResponse($user, $professional, 'Contacts saved successfully.');
    }

    /**
     * Helper to resolve current user and associated professional.
     */
    private function resolveUserAndProfessional(Request $request): array
    {
        $user = $request->user();
        $professional = null;

        if ($user) {
            $professional = Professional::where('user_id', $user->id)->first();
        }

        if (! $professional) {
            $professional = Professional::first();
            if ($professional) {
                $user = User::find($professional->user_id);
            }
        }

        return [$user, $professional];
    }

    /**
     * Calculate step-by-step completion status.
     */
    private function calculateStepStatus(User $user, Professional $professional): array
    {
        $step1Speciality = ! empty($professional->professional_speciality_code) && ! empty($professional->license_number);
        $step2Location = ! empty($professional->wilaya_code) && ! empty($professional->city) && ! empty($professional->address);
        $step3Services = $professional->services()->count() > 0;
        $step4Schedule = $professional->schedules()->where('is_active', true)->count() > 0;
        $step5Contacts = ! empty($professional->phone_public) || $professional->contacts()->count() > 0 || ! empty($user->phone_number);

        $steps = [
            1 => [
                'name' => 'speciality',
                'title' => 'Spécialité & Licence',
                'is_completed' => $step1Speciality,
            ],
            2 => [
                'name' => 'location',
                'title' => 'Wilaya & Adresse du cabinet',
                'is_completed' => $step2Location,
            ],
            3 => [
                'name' => 'services',
                'title' => 'Prestations & Tarifs',
                'is_completed' => $step3Services,
            ],
            4 => [
                'name' => 'schedule',
                'title' => 'Horaires d\'ouverture',
                'is_completed' => $step4Schedule,
            ],
            5 => [
                'name' => 'contacts',
                'title' => 'Téléphone public & Coordonnées',
                'is_completed' => $step5Contacts,
            ],
        ];

        $currentStep = 1;
        for ($i = 1; $i <= 5; $i++) {
            if (! $steps[$i]['is_completed']) {
                $currentStep = $i;
                break;
            }
            if ($i === 5 && $steps[5]['is_completed']) {
                $currentStep = 5;
            }
        }

        $allCompleted = $step1Speciality && $step2Location && $step3Services && $step4Schedule && $step5Contacts;

        return [
            'is_completed' => $allCompleted,
            'current_step' => $currentStep,
            'steps' => $steps,
        ];
    }

    /**
     * Helper to return standard onboarding step response.
     */
    private function buildOnboardingResponse(User $user, Professional $professional, string $message): JsonResponse
    {
        $professional->load(['specialty', 'wilaya', 'services', 'schedules', 'contacts']);
        $stepStatus = $this->calculateStepStatus($user, $professional);
        $isComplete = $stepStatus['is_completed'];

        $user->profile_complete = $isComplete;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => $message,
            'is_completed' => $isComplete,
            'current_step' => $stepStatus['current_step'],
            'total_steps' => 5,
            'steps' => $stepStatus['steps'],
        ]);
    }
}
