<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'enabled' => env('CMS_CACHE_ENABLED', true),
        'driver' => env('CMS_CACHE_DRIVER', 'file'), // file, database, redis
        'ttl' => env('CMS_CACHE_TTL', 3600), // 1 hour in seconds
        'prefix' => env('CMS_CACHE_PREFIX', 'cms_'),

        // Model scan cache
        'model_scan_ttl' => 3600, // 1 hour

        // Translation cache
        'translation_ttl' => 7200, // 2 hours

        // Content cache
        'content_ttl' => 1800, // 30 minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Content Models Registration
    |--------------------------------------------------------------------------
    */
    'models' => [
        // Auto-discover models in this namespace
        'namespace' => 'App\\CMS\\ContentModels',

        // Manually register models
        'register' => [
            // App\CMS\ContentModels\Page::class,
            // App\CMS\ContentModels\Post::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Values
    |--------------------------------------------------------------------------
    */
    'defaults' => [
        'status' => 'draft',
        'locale' => 'en',
        'per_page' => 15,
        'excerpt_length' => 200,
    ],

    /*
    |--------------------------------------------------------------------------
    | Slug Configuration
    |--------------------------------------------------------------------------
    */
    'slug' => [
        'separator' => '-',
        'unique' => true,
        'source_field' => 'title',
        'max_length' => 200,
    ],

    /*
    |--------------------------------------------------------------------------
    | Media Library
    |--------------------------------------------------------------------------
    */
    'media' => [
        'disk' => env('CMS_MEDIA_DISK', 'public'),
        'path' => 'media',
        'max_file_size' => 10240, // 10MB in KB
        'allowed_mime_types' => [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'application/pdf',
        ],

        // Image processing
        'image' => [
            'driver' => 'gd', // gd or imagick
            'quality' => 85,
            'webp_quality' => 80,
            'thumbnails' => [
                'small' => [150, 150],
                'medium' => [300, 300],
                'large' => [800, 800],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | SEO Configuration
    |--------------------------------------------------------------------------
    */
    'seo' => [
        'default_schema_type' => 'Thing',
        'sitemap_enabled' => true,
        'robots_enabled' => true,
        'canonical_enabled' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Panel
    |--------------------------------------------------------------------------
    */
    'admin' => [
        'prefix' => 'admin',
        'middleware' => ['web', 'auth'],
        'per_page' => 20,
    ],

    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    */
    'api' => [
        'enabled' => env('CMS_API_ENABLED', true),
        'prefix' => 'api/cms',
        'middleware' => ['api'],
        'rate_limit' => '60,1', // 60 requests per minute
    ],
];
