<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Support & Contact Configuration
    |--------------------------------------------------------------------------
    |
    | Support contact details provided to the mobile app for partners and patients.
    | Phone and email can be null or customized via environment variables.
    |
    */

    'partner' => [
        'phone' => env('SUPPORT_PARTNER_PHONE', '0551223344'),
        'phone_display' => env('SUPPORT_PARTNER_PHONE_DISPLAY', '0551 22 33 44'),
        'email' => env('SUPPORT_PARTNER_EMAIL', 'partner@rendee.dz'),
    ],

    'patient' => [
        'phone' => env('SUPPORT_PATIENT_PHONE', '0550000000'),
        'phone_display' => env('SUPPORT_PATIENT_PHONE_DISPLAY', '0550 00 00 00'),
        'email' => env('SUPPORT_PATIENT_EMAIL', 'support@rendee.dz'),
    ],
];
