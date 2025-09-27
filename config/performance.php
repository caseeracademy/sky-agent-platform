<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Performance Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains performance-related configurations for the
    | Sky Education Portal application. These settings help optimize
    | the application for production use.
    |
    */

    'opcache' => [
        /*
        |--------------------------------------------------------------------------
        | OPcache Settings
        |--------------------------------------------------------------------------
        |
        | These settings control PHP OPcache behavior. OPcache stores precompiled
        | script bytecode in shared memory, thereby removing the need for PHP to
        | load and parse scripts on each request.
        |
        */
        'enable' => env('OPCACHE_ENABLE', true),
        'enable_cli' => env('OPCACHE_ENABLE_CLI', false),
        'memory_consumption' => env('OPCACHE_MEMORY_CONSUMPTION', 128),
        'interned_strings_buffer' => env('OPCACHE_INTERNED_STRINGS_BUFFER', 8),
        'max_accelerated_files' => env('OPCACHE_MAX_ACCELERATED_FILES', 4000),
        'revalidate_freq' => env('OPCACHE_REVALIDATE_FREQ', 60),
        'fast_shutdown' => env('OPCACHE_FAST_SHUTDOWN', 1),
    ],

    'database' => [
        /*
        |--------------------------------------------------------------------------
        | Database Connection Pool
        |--------------------------------------------------------------------------
        |
        | Configure database connection pooling for better performance.
        |
        */
        'pool_size' => env('DB_POOL_SIZE', 10),
        'max_connections' => env('DB_MAX_CONNECTIONS', 100),
        'connection_timeout' => env('DB_CONNECTION_TIMEOUT', 30),
        'read_timeout' => env('DB_READ_TIMEOUT', 60),
        'write_timeout' => env('DB_WRITE_TIMEOUT', 60),

        /*
        |--------------------------------------------------------------------------
        | Query Performance
        |--------------------------------------------------------------------------
        |
        | Settings to optimize database query performance.
        |
        */
        'slow_query_threshold' => env('DB_SLOW_QUERY_THRESHOLD', 2000), // milliseconds
        'enable_query_log' => env('DB_ENABLE_QUERY_LOG', false),
        'log_slow_queries' => env('DB_LOG_SLOW_QUERIES', true),
    ],

    'cache' => [
        /*
        |--------------------------------------------------------------------------
        | Cache Performance Settings
        |--------------------------------------------------------------------------
        |
        | Configure caching behavior for optimal performance.
        |
        */
        'default_ttl' => env('CACHE_DEFAULT_TTL', 3600), // 1 hour
        'long_ttl' => env('CACHE_LONG_TTL', 86400), // 24 hours
        'short_ttl' => env('CACHE_SHORT_TTL', 300), // 5 minutes
        
        /*
        |--------------------------------------------------------------------------
        | Cache Tags
        |--------------------------------------------------------------------------
        |
        | Define cache tags for easier cache invalidation.
        |
        */
        'tags' => [
            'users' => 'users',
            'students' => 'students',
            'applications' => 'applications',
            'programs' => 'programs',
            'universities' => 'universities',
            'commissions' => 'commissions',
        ],
    ],

    'session' => [
        /*
        |--------------------------------------------------------------------------
        | Session Performance Settings
        |--------------------------------------------------------------------------
        |
        | Configure session handling for better performance and security.
        |
        */
        'cleanup_probability' => env('SESSION_CLEANUP_PROBABILITY', 1),
        'cleanup_divisor' => env('SESSION_CLEANUP_DIVISOR', 100),
        'gc_maxlifetime' => env('SESSION_GC_MAXLIFETIME', 7200), // 2 hours
    ],

    'view' => [
        /*
        |--------------------------------------------------------------------------
        | View Compilation Settings
        |--------------------------------------------------------------------------
        |
        | Configure view compilation and caching behavior.
        |
        */
        'cache_views' => env('VIEW_CACHE_VIEWS', true),
        'compile_check' => env('VIEW_COMPILE_CHECK', false), // Disable in production
    ],

    'assets' => [
        /*
        |--------------------------------------------------------------------------
        | Asset Performance Settings
        |--------------------------------------------------------------------------
        |
        | Configure asset handling and optimization.
        |
        */
        'versioning' => env('ASSET_VERSIONING', true),
        'minify_css' => env('ASSET_MINIFY_CSS', true),
        'minify_js' => env('ASSET_MINIFY_JS', true),
        'combine_files' => env('ASSET_COMBINE_FILES', true),
        'cdn_url' => env('ASSET_CDN_URL', null),
    ],

    'api' => [
        /*
        |--------------------------------------------------------------------------
        | API Performance Settings
        |--------------------------------------------------------------------------
        |
        | Configure API response caching and optimization.
        |
        */
        'cache_responses' => env('API_CACHE_RESPONSES', true),
        'cache_ttl' => env('API_CACHE_TTL', 300), // 5 minutes
        'rate_limit' => env('API_RATE_LIMIT', 60), // requests per minute
        'enable_etag' => env('API_ENABLE_ETAG', true),
        'enable_compression' => env('API_ENABLE_COMPRESSION', true),
    ],

    'monitoring' => [
        /*
        |--------------------------------------------------------------------------
        | Performance Monitoring
        |--------------------------------------------------------------------------
        |
        | Enable performance monitoring and profiling.
        |
        */
        'enable_profiling' => env('ENABLE_PROFILING', false),
        'profile_percentage' => env('PROFILE_PERCENTAGE', 1), // 1% of requests
        'memory_limit_warning' => env('MEMORY_LIMIT_WARNING', 200), // MB
        'execution_time_warning' => env('EXECUTION_TIME_WARNING', 5000), // milliseconds
    ],

    'queue' => [
        /*
        |--------------------------------------------------------------------------
        | Queue Performance Settings
        |--------------------------------------------------------------------------
        |
        | Configure queue processing for optimal performance.
        |
        */
        'workers' => env('QUEUE_WORKERS', 3),
        'timeout' => env('QUEUE_TIMEOUT', 60),
        'retry_after' => env('QUEUE_RETRY_AFTER', 90),
        'max_tries' => env('QUEUE_MAX_TRIES', 3),
        'batch_size' => env('QUEUE_BATCH_SIZE', 100),
    ],

];
