<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | هذا الملف يحدد إعدادات الـ CORS لتطبيقك.
    | يمكنك السماح لأي دومين أو تقييدها بدومينات محددة.
    |
    */

    'paths' => ['api/*', 'storage/*', 'sanctum/csrf-cookie', 'login', 'logout'],

    'allowed_methods' => ['*'],


    'allowed_origins' => ['https://shopess.store'],


    'allowed_origins_patterns' => [],


    'allowed_headers' => ['*'],


    'exposed_headers' => [],


    'max_age' => 0,


    'supports_credentials' => true,

];
