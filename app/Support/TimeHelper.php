<?php

namespace App\Support;

use Carbon\Carbon;
use Throwable;

class TimeHelper
{
    /**
     * Normalize various time formats (including Arabic localized strings and numerals)
     * into standard 24-hour SQL time format 'H:i:s'.
     */
    public static function normalize(?string $time): ?string
    {
        if ($time === null) {
            return null;
        }

        $time = trim($time);
        if ($time === '') {
            return null;
        }

        // Convert Eastern Arabic-Indic numerals and Persian numerals to standard ASCII digits
        $easternNumerals = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $persianNumerals = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $asciiDigits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        $time = str_replace($easternNumerals, $asciiDigits, $time);
        $time = str_replace($persianNumerals, $asciiDigits, $time);

        // Remove common Unicode direction marks and control characters
        $time = preg_replace('/[\x{200E}\x{200F}\x{202A}-\x{202E}\x{FEFF}]/u', '', $time) ?? $time;
        $time = preg_replace('/\s+/u', ' ', $time) ?? $time;
        $time = trim($time);

        // Check for PM indicators (Arabic & English)
        $isPm = (bool) preg_match('/(مساءً|مساءا|مساء|م(?![a-zA-Z])|pm|p\.m\.)/iu', $time);

        // Check for AM indicators (Arabic & English)
        $isAm = (bool) preg_match('/(صباحاً|صباحا|صباح|ص(?![a-zA-Z])|am|a\.m\.)/iu', $time);

        // Extract numbers HH:MM(:SS)?
        if (preg_match('/(\d{1,2}):(\d{2})(?::(\d{2}))?/', $time, $matches)) {
            $hour = (int) $matches[1];
            $minute = (int) $matches[2];
            $second = isset($matches[3]) ? (int) $matches[3] : 0;

            if ($isPm) {
                if ($hour < 12) {
                    $hour += 12;
                }
            } elseif ($isAm) {
                if ($hour === 12) {
                    $hour = 0;
                }
            }

            // Ensure valid hour, minute, second
            $hour = max(0, min(23, $hour));
            $minute = max(0, min(59, $minute));
            $second = max(0, min(59, $second));

            return sprintf('%02d:%02d:%02d', $hour, $minute, $second);
        }

        // Fallback: try Carbon parsing
        try {
            return Carbon::parse($time)->format('H:i:s');
        } catch (Throwable) {
            return $time;
        }
    }
}
