<?php

use App\Support\TimeHelper;

test('it normalizes Arabic and English time formats correctly', function (string $input, string $expected) {
    expect(TimeHelper::normalize($input))->toBe($expected);
})->with([
    'Arabic PM with word before' => ['مساءً 02:00', '14:00:00'],
    'Arabic PM with word after' => ['02:00 مساءً', '14:00:00'],
    'Arabic PM variation masa' => ['02:00 مساء', '14:00:00'],
    'Arabic PM abbreviation' => ['02:00 م', '14:00:00'],
    'Arabic AM with word before' => ['صباحاً 09:30', '09:30:00'],
    'Arabic AM with word after' => ['09:30 صباحاً', '09:30:00'],
    'Arabic AM variation sabah' => ['09:30 صباح', '09:30:00'],
    'Arabic AM abbreviation' => ['09:30 ص', '09:30:00'],
    'Arabic 12 PM noon' => ['12:00 مساءً', '12:00:00'],
    'Arabic 12 AM midnight' => ['12:00 صباحاً', '00:00:00'],
    'Eastern Arabic numerals PM' => ['٠٢:٣٠ مساءً', '14:30:00'],
    'Eastern Arabic numerals 24h' => ['١٤:١٥', '14:15:00'],
    'English 12h PM' => ['02:00 PM', '14:00:00'],
    'English 12h AM' => ['02:00 AM', '02:00:00'],
    'Standard 24h HH:MM' => ['14:00', '14:00:00'],
    'Standard 24h HH:MM:SS' => ['14:00:00', '14:00:00'],
    'Single digit hour' => ['9:00', '09:00:00'],
]);
