<?php

// config for Drmmr763/AsyncApi
return [
    /*
    |--------------------------------------------------------------------------
    | AsyncAPI Specification Version
    |--------------------------------------------------------------------------
    |
    | The AsyncAPI specification version to use for generation.
    |
    */
    'version' => env('ASYNCAPI_VERSION', '3.0.0'),

    /*
    |--------------------------------------------------------------------------
    | Default Content Type
    |--------------------------------------------------------------------------
    |
    | The default content type for messages if not specified.
    |
    */
    'default_content_type' => env('ASYNCAPI_DEFAULT_CONTENT_TYPE', 'application/json'),

    /*
    |--------------------------------------------------------------------------
    | Scan Paths
    |--------------------------------------------------------------------------
    |
    | Paths to scan for AsyncAPI annotations. By default, scans the app directory.
    |
    */
    'scan_paths' => [
        app_path(),
    ],

    /*
    |--------------------------------------------------------------------------
    | Output Path
    |--------------------------------------------------------------------------
    |
    | Default path where generated AsyncAPI specification files will be saved.
    |
    */
    'output_path' => base_path('asyncapi'),

    /*
    |--------------------------------------------------------------------------
    | Export Formats
    |--------------------------------------------------------------------------
    |
    | Available export formats for AsyncAPI specifications.
    | Supported: 'json', 'yaml'
    |
    */
    'export_formats' => ['json', 'yaml'],

    /*
    |--------------------------------------------------------------------------
    | Default Export Format
    |--------------------------------------------------------------------------
    |
    | The default format to use when exporting AsyncAPI specifications.
    |
    */
    'default_export_format' => env('ASYNCAPI_EXPORT_FORMAT', 'yaml'),

    /*
    |--------------------------------------------------------------------------
    | Pretty Print
    |--------------------------------------------------------------------------
    |
    | Whether to pretty print the exported AsyncAPI specifications.
    |
    */
    'pretty_print' => env('ASYNCAPI_PRETTY_PRINT', true),

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    |
    | Enable caching of scanned annotations for better performance.
    |
    */
    'cache' => [
        'enabled' => env('ASYNCAPI_CACHE_ENABLED', true),
        'ttl' => env('ASYNCAPI_CACHE_TTL', 3600), // 1 hour
        'key' => 'asyncapi_annotations',
    ],
];
