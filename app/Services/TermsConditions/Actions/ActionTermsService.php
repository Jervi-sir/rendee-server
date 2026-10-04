<?php

namespace App\Services\TermsConditions\Actions;

use App\Services\TermsConditions\BaseTermsService;

class ActionTermsService extends BaseTermsService
{
    /**
     * Get toggleable action/consent components for a locale and optional target group.
     *
     * @param  string|null  $locale
     * @param  string|null  $target
     * @return array<string, mixed>
     */
    public function get(?string $locale = null, ?string $target = null): array
    {
        $normalizedLocale = $this->normalizeLocale($locale);
        $data = $this->loadFile(__DIR__, $normalizedLocale);
        $groups = $data['groups'] ?? [];

        if ($target && isset($groups[$target])) {
            return [
                'locale' => $normalizedLocale,
                'available_locales' => $this->getSupportedLocales(),
                'target' => $target,
                'data' => $groups[$target],
            ];
        }

        return [
            'locale' => $normalizedLocale,
            'available_locales' => $this->getSupportedLocales(),
            'target' => 'all',
            'groups' => array_values($groups),
        ];
    }

    /**
     * Get a specific action group (e.g., medical_record).
     */
    public function getGroup(string $target, ?string $locale = null): ?array
    {
        $normalizedLocale = $this->normalizeLocale($locale);
        $data = $this->loadFile(__DIR__, $normalizedLocale);

        return $data['groups'][$target] ?? null;
    }
}
