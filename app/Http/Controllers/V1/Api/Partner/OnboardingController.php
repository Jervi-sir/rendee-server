<?php

namespace App\Http\Controllers\V1\Api\Partner;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Models\PartnerSchedule;
use App\Models\PartnerService;
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
        $partner = null;

        if ($user) {
            $partner = Partner::with(['specialty', 'wilaya', 'services', 'schedules', 'contacts'])
                ->where('user_id', $user->id)
                ->first();
        }

        if (! $partner) {
            $partner = Partner::with(['specialty', 'wilaya', 'services', 'schedules', 'contacts'])->first();
            if ($partner) {
                $user = User::find($partner->user_id);
            }
        }

        if (! $partner || ! $user) {
            return response()->json([
                'success' => false,
                'error' => 'Partner profile not found',
            ], 404);
        }

        $stepStatus = $this->calculateStepStatus($user, $partner);
        $isComplete = $stepStatus['is_completed'];

        // Update profile_completed flag if changed
        if ($user->profile_completed !== $isComplete) {
            $user->profile_completed = $isComplete;
            $user->save();
        }

        return response()->json([
            'success' => true,
            'is_completed' => $isComplete,
            'current_step' => $stepStatus['current_step'],
            'total_steps' => 4,
            'sections' => $stepStatus['sections'],
            'steps' => $stepStatus['steps'],
            'data' => [
                'speciality_code' => $partner->speciality_code,
                'custom_speciality' => $partner->custom_speciality,
                'speciality_name' => $partner->display_speciality,
                'license_number' => $partner->license_number,
                'years_experience' => $partner->years_experience,
                'bio' => $partner->bio,
                'wilaya_code' => $partner->wilaya_code,
                'wilaya_name' => $partner->wilaya?->ar ?? $partner->wilaya?->en ?? null,
                'city' => $partner->city,
                'address' => $partner->address,
                'latitude' => $partner->latitude ? (float) $partner->latitude : null,
                'longitude' => $partner->longitude ? (float) $partner->longitude : null,
                'services_count' => $partner->services->count(),
                'schedules_count' => $partner->schedules->where('is_active', true)->count(),
                'contacts_count' => $partner->contacts->count(),
            ],
        ]);
    }

    /**
     * POST endpoint to verify and save onboarding completion status.
     */
    public function complete(Request $request): JsonResponse
    {
        [$user, $partner] = $this->resolveUserAndPartner($request);

        if (! $partner || ! $user) {
            return response()->json([
                'success' => false,
                'error' => 'Partner profile not found',
            ], 404);
        }

        $stepStatus = $this->calculateStepStatus($user, $partner);
        $isComplete = $stepStatus['is_completed'];

        $user->profile_completed = $isComplete;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => $isComplete ? 'Onboarding completed successfully' : 'Onboarding saved',
            'is_completed' => $isComplete,
            'current_step' => $stepStatus['current_step'],
            'total_steps' => 5,
            'steps' => $stepStatus['steps'],
        ]);
    }

    /**
     * Step 1: Upsert Specialty and Partner Information.
     */
    public function stepSpeciality(Request $request): JsonResponse
    {
        [$user, $partner] = $this->resolveUserAndPartner($request);
        if (! $partner || ! $user) {
            return response()->json(['error' => 'Partner profile not found'], 404);
        }

        $validated = $request->validate([
            'speciality_code' => ['nullable', 'string', Rule::exists('specialities', 'code')],
            'professional_speciality_code' => ['nullable', 'string', Rule::exists('specialities', 'code')],
            'custom_speciality' => ['nullable', 'string', 'max:255'],
            'specialty_id' => ['nullable', 'string'],
            'license_number' => ['nullable', 'string', 'max:100'],
            'years_experience' => ['nullable', 'string', 'max:10'],
            'bio' => ['nullable', 'string'],
        ]);

        $specialityCode = $validated['speciality_code'] ?? $validated['professional_speciality_code'] ?? $validated['specialty_id'] ?? null;

        if (array_key_exists('speciality_code', $validated) || array_key_exists('professional_speciality_code', $validated) || array_key_exists('specialty_id', $validated)) {
            $partner->speciality_code = $specialityCode;
        }
        if (array_key_exists('custom_speciality', $validated)) {
            $partner->custom_speciality = $validated['custom_speciality'];
        }
        if (array_key_exists('license_number', $validated)) {
            $partner->license_number = $validated['license_number'];
        }
        if (array_key_exists('years_experience', $validated)) {
            $partner->years_experience = $validated['years_experience'];
        }
        if (array_key_exists('bio', $validated)) {
            $partner->bio = $validated['bio'];
        }
        $partner->save();

        return $this->buildOnboardingResponse($user, $partner, 'Speciality details saved successfully.');
    }

    /**
     * Step 2: Upsert Location & Wilaya.
     */
    public function stepLocation(Request $request): JsonResponse
    {
        [$user, $partner] = $this->resolveUserAndPartner($request);
        if (! $partner || ! $user) {
            return response()->json(['error' => 'Partner profile not found'], 404);
        }

        $validated = $request->validate([
            'wilaya_code' => ['required', 'string', Rule::exists('wilayas', 'code')],
            'city' => ['required', 'string', 'max:100'],
            'address' => ['required', 'string', 'max:500'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
        ]);

        $partner->wilaya_code = $validated['wilaya_code'];
        $partner->city = $validated['city'];
        $partner->address = $validated['address'];
        if (array_key_exists('latitude', $validated)) {
            $partner->latitude = $validated['latitude'];
        }
        if (array_key_exists('longitude', $validated)) {
            $partner->longitude = $validated['longitude'];
        }
        $partner->save();

        return $this->buildOnboardingResponse($user, $partner, 'Location details saved successfully.');
    }

    /**
     * Step 3: Upsert Services.
     */
    public function stepServices(Request $request): JsonResponse
    {
        [$user, $partner] = $this->resolveUserAndPartner($request);
        if (! $partner || ! $user) {
            return response()->json(['error' => 'Partner profile not found'], 404);
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
                $baseCode = 'partner_'.Str::slug($name, '_');
                $catalog = ServiceCatalog::firstOrCreate(
                    ['code' => $baseCode],
                    ['source' => 'partner', 'ar' => $name, 'en' => $name]
                );
                $catalogCode = $catalog->code;
            }

            if ($catalogCode) {
                PartnerService::updateOrCreate(
                    [
                        'partner_id' => $partner->id,
                        'service_catalog_code' => $catalogCode,
                    ],
                    [
                        'name' => $name,
                        'price' => $svcItem['price'],
                        'duration_minutes' => $svcItem['duration_minutes'] ?? 30,
                    ]
                );
            }
        }

        return $this->buildOnboardingResponse($user, $partner, 'Services saved successfully.');
    }

    /**
     * Step 4: Upsert Schedule.
     */
    public function stepSchedule(Request $request): JsonResponse
    {
        [$user, $partner] = $this->resolveUserAndPartner($request);
        if (! $partner || ! $user) {
            return response()->json(['error' => 'Partner profile not found'], 404);
        }

        $validated = $request->validate([
            'schedules' => ['required', 'array', 'min:1'],
            'schedules.*.day_of_week' => ['required', 'integer', 'between:0,6'],
            'schedules.*.start_time' => ['nullable', 'string'],
            'schedules.*.end_time' => ['nullable', 'string'],
            'schedules.*.is_active' => ['required', 'boolean'],
        ]);

        foreach ($validated['schedules'] as $sch) {
            PartnerSchedule::updateOrCreate(
                [
                    'partner_id' => $partner->id,
                    'day_of_week' => (int) $sch['day_of_week'],
                ],
                [
                    'start_time' => $sch['start_time'] ?? '08:00',
                    'end_time' => $sch['end_time'] ?? '17:00',
                    'is_active' => (bool) $sch['is_active'],
                ]
            );
        }

        return $this->buildOnboardingResponse($user, $partner, 'Schedule saved successfully.');
    }

    /**
     * Step 5: Upsert Contacts.
     */
    public function stepContacts(Request $request): JsonResponse
    {
        [$user, $partner] = $this->resolveUserAndPartner($request);
        if (! $partner || ! $user) {
            return response()->json(['error' => 'Partner profile not found'], 404);
        }

        $validated = $request->validate([
            'phone_public' => ['nullable', 'string', 'max:30'],
            'contacts' => ['nullable', 'array'],
            'contacts.*.platform_code' => ['required', 'string', Rule::exists('contact_platforms', 'code')],
            'contacts.*.url' => ['required', 'string', 'max:500'],
        ]);

        if (! empty($validated['phone_public'])) {
            $partner->phone_public = $validated['phone_public'];
            $partner->save();
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
                        'target_user_type' => 'partner',
                    ]
                );
            }
        }

        return $this->buildOnboardingResponse($user, $partner, 'Contacts saved successfully.');
    }

    /**
     * Helper to resolve current user and associated partner.
     */
    private function resolveUserAndPartner(Request $request): array
    {
        $user = $request->user();
        $partner = null;

        if ($user) {
            $partner = Partner::where('user_id', $user->id)->first();
        }

        if (! $partner) {
            $partner = Partner::first();
            if ($partner) {
                $user = User::find($partner->user_id);
            }
        }

        return [$user, $partner];
    }

    /**
     * Calculate step-by-step & section completion status.
     */
    private function calculateStepStatus(User $user, Partner $partner): array
    {
        $hasProfile = ! empty($user->full_name) && ! empty($user->email) && (! empty($user->phone_number) || ! empty($partner->phone_public)) && (! empty($partner->address) || ! empty($partner->city));
        $hasSchedule = $partner->schedules()->where('is_active', true)->count() > 0;
        $hasServices = $partner->services()->count() > 0;
        $hasLocation = ! empty($partner->latitude) && ! empty($partner->longitude);

        $sections = [
            'profile' => [
                'completed' => $hasProfile,
                'missing_count' => $hasProfile ? 0 : 1,
            ],
            'schedule' => [
                'completed' => $hasSchedule,
                'missing_count' => $hasSchedule ? 0 : 1,
            ],
            'services' => [
                'completed' => $hasServices,
                'missing_count' => $hasServices ? 0 : 1,
            ],
            'location' => [
                'completed' => $hasLocation,
                'missing_count' => $hasLocation ? 0 : 1,
            ],
        ];

        $step1Speciality = ! empty($partner->speciality_code) || ! empty($partner->custom_speciality) || ! empty($partner->license_number) || ! empty($partner->center_catalog_code);
        $step2Location = $hasLocation;
        $step3Services = $hasServices;
        $step4Schedule = $hasSchedule;
        $step5Contacts = ! empty($partner->phone_public) || $partner->contacts()->count() > 0 || ! empty($user->phone_number);

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

        $allCompleted = $hasProfile && $hasSchedule && $hasServices && $hasLocation;

        return [
            'is_completed' => $allCompleted,
            'current_step' => $currentStep,
            'sections' => $sections,
            'steps' => $steps,
        ];
    }

    /**
     * Helper to return standard onboarding step response.
     */
    private function buildOnboardingResponse(User $user, Partner $partner, string $message): JsonResponse
    {
        $partner->load(['specialty', 'wilaya', 'services', 'schedules', 'contacts']);
        $stepStatus = $this->calculateStepStatus($user, $partner);
        $isComplete = $stepStatus['is_completed'];

        $user->profile_completed = $isComplete;
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
