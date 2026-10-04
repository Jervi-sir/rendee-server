<?php

namespace App\Services\TermsConditions\Partner;

use App\Services\TermsConditions\Actions\ActionTermsService;
use App\Services\TermsConditions\BaseTermsService;

class PartnerTermsService extends BaseTermsService
{
    public function __construct(
        protected ActionTermsService $actionTermsService
    ) {}

    /**
     * Get partner terms, privacy policy, and relevant toggleable actions.
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
            'audience' => 'partner',
            'locale' => $normalizedLocale,
            'available_locales' => $this->getSupportedLocales(),
            'last_updated' => $data['last_updated'] ?? '2026-10-05',
            'effective_date' => $data['effective_date'] ?? '2026-10-05',
            'terms' => $data['terms'] ?? [],
            'privacy_policy' => $data['privacy_policy'] ?? [],
        ];

        if ($includeActions) {
            $partnerActions = $this->actionTermsService->getGroup('partner_onboarding', $normalizedLocale);
            if ($partnerActions) {
                $response['actions'] = [$partnerActions];
            }
        }

        return $response;
    }

    /**
     * Get only terms of service for partner.
     */
    public function getTerms(?string $locale = null): array
    {
        $data = $this->loadFile(__DIR__, $this->normalizeLocale($locale));

        return $data['terms'] ?? [];
    }

    /**
     * Get only privacy policy for partner.
     */
    public function getPrivacyPolicy(?string $locale = null): array
    {
        $data = $this->loadFile(__DIR__, $this->normalizeLocale($locale));

        return $data['privacy_policy'] ?? [];
    }
}
