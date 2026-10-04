<?php

namespace App\Services\TermsConditions\Patient;

use App\Services\TermsConditions\Actions\ActionTermsService;
use App\Services\TermsConditions\BaseTermsService;

class PatientTermsService extends BaseTermsService
{
    public function __construct(
        protected ActionTermsService $actionTermsService
    ) {}

    /**
     * Get patient terms, privacy policy, and relevant toggleable actions.
     *
     * @param  string|null  $locale
     * @param  bool  $includeActions
     * @return array<string, mixed>
     */
    public function get(?string $locale = null, bool $includeActions = true): array
    {
        $normalizedLocale = $this->normalizeLocale($locale);
        $data = $this->loadFile(__DIR__, $normalizedLocale);

        $response = [
            'audience' => 'patient',
            'locale' => $normalizedLocale,
            'available_locales' => $this->getSupportedLocales(),
            'last_updated' => $data['last_updated'] ?? '2026-10-05',
            'effective_date' => $data['effective_date'] ?? '2026-10-05',
            'terms' => $data['terms'] ?? [],
            'privacy_policy' => $data['privacy_policy'] ?? [],
        ];

        if ($includeActions) {
            $registrationActions = $this->actionTermsService->getGroup('patient_registration', $normalizedLocale);
            $medicalRecordActions = $this->actionTermsService->getGroup('medical_record', $normalizedLocale);

            $response['actions'] = array_values(array_filter([
                $registrationActions,
                $medicalRecordActions,
            ]));
        }

        return $response;
    }

    /**
     * Get only terms of service for patient.
     */
    public function getTerms(?string $locale = null): array
    {
        $data = $this->loadFile(__DIR__, $this->normalizeLocale($locale));

        return $data['terms'] ?? [];
    }

    /**
     * Get only privacy policy for patient.
     */
    public function getPrivacyPolicy(?string $locale = null): array
    {
        $data = $this->loadFile(__DIR__, $this->normalizeLocale($locale));

        return $data['privacy_policy'] ?? [];
    }
}
