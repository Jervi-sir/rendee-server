<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Mobile App Versions & Store Links
    |--------------------------------------------------------------------------
    |
    | Define the minimum required version and the latest available version for
    | each platform. Any client version below min_version will be forced to
    | update before being allowed to proceed into the application.
    |
    */

    'android' => [
        'min_version' => env('MOBILE_ANDROID_MIN_VERSION', '1.0.0'),
        'latest_version' => env('MOBILE_ANDROID_LATEST_VERSION', '1.0.0'),
        'store_url' => env('MOBILE_ANDROID_STORE_URL', 'https://play.google.com/store/apps/details?id=com.rendee.app'),
    ],

    'ios' => [
        'min_version' => env('MOBILE_IOS_MIN_VERSION', '1.0.0'),
        'latest_version' => env('MOBILE_IOS_LATEST_VERSION', '1.0.0'),
        'store_url' => env('MOBILE_IOS_STORE_URL', 'https://apps.apple.com/app/rendee/idXXXXXXXXX'),
    ],

    'messages' => [
        'force_title' => 'تحديث إجباري متوفر',
        'force_message' => 'يتوفر إصدار جديد وهام من تطبيق راندي. يرجى التحديث إلى أحدث إصدار للمتابعة والاستفادة من التحسينات الجديدة.',
        'optional_title' => 'تحديث جديد متوفر',
        'optional_message' => 'يتوفر إصدار أحدث من تطبيق راندي مع ميزات وتحسينات جديدة.',
        'button_text' => 'تحديث الآن',
    ],
];
