<?php

namespace App\Http\Controllers\V1\Api\TermsConditions;

use App\Http\Controllers\Controller;
use App\Services\TermsConditions\Actions\ActionTermsService;
use App\Services\TermsConditions\Partner\PartnerTermsService;
use App\Services\TermsConditions\Patient\PatientTermsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TermsConditionsController extends Controller
{
    public function __construct(
        protected PatientTermsService $patientTermsService,
        protected PartnerTermsService $partnerTermsService,
        protected ActionTermsService $actionTermsService
    ) {}

    /**
     * General terms & conditions endpoint.
     *
     * Supports:
     * - ?audience=patient|partner|actions|all
     * - ?lang=ar|fr|en (also supports locale header or Accept-Language)
     * - ?with_actions=true|false
     *
     * Example: GET /api/v1/terms?audience=patient&lang=fr
     */
    public function index(Request $request): JsonResponse
    {
        $locale = $this->resolveLocale($request);
        $audience = strtolower(trim((string) $request->query('audience', $request->query('type', 'all'))));
        $withActions = $request->boolean('with_actions', true);

        return match ($audience) {
            'patient' => response()->json($this->patientTermsService->get($locale, $withActions)),
            'partner' => response()->json($this->partnerTermsService->get($locale, $withActions)),
            'actions' => response()->json($this->actionTermsService->get($locale, $request->query('target'))),
            default => response()->json([
                'locale' => $locale,
                'available_locales' => $this->patientTermsService->getSupportedLocales(),
                'patient' => $this->patientTermsService->get($locale, $withActions),
                'partner' => $this->partnerTermsService->get($locale, $withActions),
            ]),
        };
    }

    /**
     * Get patient terms, privacy policy, and medical consent toggle actions.
     *
     * Example: GET /api/v1/terms/patient?lang=ar
     */
    public function patient(Request $request): JsonResponse
    {
        $locale = $this->resolveLocale($request);
        $withActions = $request->boolean('with_actions', true);

        return response()->json($this->patientTermsService->get($locale, $withActions));
    }

    /**
     * Get partner terms, privacy policy, subscription terms, and onboarding actions.
     *
     * Example: GET /api/v1/terms/partner?lang=en
     */
    public function partner(Request $request): JsonResponse
    {
        $locale = $this->resolveLocale($request);
        $withActions = $request->boolean('with_actions', true);

        return response()->json($this->partnerTermsService->get($locale, $withActions));
    }

    /**
     * Get toggleable consent actions intercepted by frontend toggle/switch components.
     *
     * Supports:
     * - ?target=medical_record|patient_registration|partner_onboarding|all
     * - ?lang=ar|fr|en
     *
     * Example: GET /api/v1/terms/actions?target=medical_record&lang=fr
     */
    public function actions(Request $request): JsonResponse
    {
        $locale = $this->resolveLocale($request);
        $target = $request->query('target');

        return response()->json($this->actionTermsService->get($locale, $target));
    }

    /**
     * Resolve locale from request query parameter or headers.
     */
    protected function resolveLocale(Request $request): string
    {
        return $request->query('lang')
            ?? $request->query('locale')
            ?? $request->query('language')
            ?? $request->header('X-Locale')
            ?? $request->header('Accept-Language')
            ?? 'ar';
    }
}
