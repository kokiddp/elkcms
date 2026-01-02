<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Language
    |--------------------------------------------------------------------------
    */
    'default' => env('CMS_DEFAULT_LANGUAGE', 'en'),

    /*
    |--------------------------------------------------------------------------
    | Fallback Language
    |--------------------------------------------------------------------------
    */
    'fallback' => env('CMS_FALLBACK_LANGUAGE', 'en'),

    /*
    |--------------------------------------------------------------------------
    | Supported Languages
    |--------------------------------------------------------------------------
    */
    'supported' => [
        'en' => [
            'name' => 'English',
            'native' => 'English',
            'flag' => '🇬🇧',
            'locale' => 'en_US',
            'enabled' => true,
        ],
        'it' => [
            'name' => 'Italian',
            'native' => 'Italiano',
            'flag' => '🇮🇹',
            'locale' => 'it_IT',
            'enabled' => true,
        ],
        'de' => [
            'name' => 'German',
            'native' => 'Deutsch',
            'flag' => '🇩🇪',
            'locale' => 'de_DE',
            'enabled' => false,
        ],
        'fr' => [
            'name' => 'French',
            'native' => 'Français',
            'flag' => '🇫🇷',
            'locale' => 'fr_FR',
            'enabled' => false,
        ],
        'es' => [
            'name' => 'Spanish',
            'native' => 'Español',
            'flag' => '🇪🇸',
            'locale' => 'es_ES',
            'enabled' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | URL Strategy
    |--------------------------------------------------------------------------
    |
    | Options: 'prefix' - /en/page, /it/page
    |          'domain' - en.example.com, it.example.com
    |          'parameter' - /page?lang=en
    */
    'url_strategy' => env('CMS_LANGUAGE_URL_STRATEGY', 'prefix'),

    /*
    |--------------------------------------------------------------------------
    | Hide Default Language in URL
    |--------------------------------------------------------------------------
    */
    'hide_default_in_url' => env('CMS_HIDE_DEFAULT_LANGUAGE', true),

    /*
    |--------------------------------------------------------------------------
    | Translation Storage
    |--------------------------------------------------------------------------
    */
    'storage' => [
        'driver' => 'database', // database or file
        'table' => 'cms_translations',
    ],

    /*
    |--------------------------------------------------------------------------
    | Language Switcher
    |--------------------------------------------------------------------------
    */
    'switcher' => [
        'show_flags' => true,
        'show_native_name' => true,
        'show_english_name' => false,
    ],
];
