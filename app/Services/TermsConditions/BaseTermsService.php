<?php

namespace App\Services\TermsConditions;

abstract class BaseTermsService
{
    /**
     * Supported locales.
     *
     * @var array<string>
     */
    protected const SUPPORTED_LOCALES = ['ar', 'fr', 'en'];

    /**
     * Default fallback locale.
     */
    protected const DEFAULT_LOCALE = 'ar';

    /**
     * Normalize and validate requested locale.
     */
    public function normalizeLocale(?string $locale): string
    {
        if (empty($locale)) {
            return self::DEFAULT_LOCALE;
        }

        $locale = strtolower(trim($locale));

        // Support formats like fr_FR, fr-DZ, en-US, ar-DZ
        $primary = explode('-', str_replace('_', '-', $locale))[0];

        if (in_array($primary, self::SUPPORTED_LOCALES, true)) {
            return $primary;
        }

        return self::DEFAULT_LOCALE;
    }

    /**
     * Get list of supported locales.
     *
     * @return array<string>
     */
    public function getSupportedLocales(): array
    {
        return self::SUPPORTED_LOCALES;
    }

    /**
     * Load translation file from a directory.
     *
     * @param  string  $basePath
     * @param  string  $locale
     * @return array<string, mixed>
     */
    protected function loadFile(string $basePath, string $locale): array
    {
        $normalized = $this->normalizeLocale($locale);
        $filePath = rtrim($basePath, '/').'/Translations/'.$normalized.'.php';

        if (file_exists($filePath)) {
            return require $filePath;
        }

        $fallbackPath = rtrim($basePath, '/').'/Translations/'.self::DEFAULT_LOCALE.'.php';
        if (file_exists($fallbackPath)) {
            return require $fallbackPath;
        }

        return [];
    }
}
