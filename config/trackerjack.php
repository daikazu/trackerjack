<?php

declare(strict_types=1);

// config for Daikazu/Trackerjack
return [
    /*
    |--------------------------------------------------------------------------
    | Cookie Settings
    |--------------------------------------------------------------------------
    |
    | Configure the cookie used to track visitors
    |
    */
    'cookie' => [
        'name' => 'trackerjack_id',
        'ttl' => 60 * 24 * 365, // 1 year in minutes
        'domain' => env('SESSION_DOMAIN'),
    ],

    /*
    |--------------------------------------------------------------------------
    | UTM Parameters
    |--------------------------------------------------------------------------
    |
    | Define which UTM parameters to track
    |
    */
    'utm_parameters' => [
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term',
        'utm_content',
        'gclid',
    ],

    /*
    |--------------------------------------------------------------------------
    | Route Exclusions
    |--------------------------------------------------------------------------
    |
    | Define routes that should not be tracked
    |
    */
    'excluded_routes' => [
        'admin/*',
        'api/*',
        'horizon/*',
        'telescope/*',
    ],

    /*
    |--------------------------------------------------------------------------
    | Cleanup Settings
    |--------------------------------------------------------------------------
    |
    | Configure how long to keep tracking data
    |
    */
    'cleanup' => [
        'visits_older_than' => 60 * 24 * 30, // 30 days in minutes
        'events_older_than' => 60 * 24 * 90, // 90 days in minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Event Whitelist
    |--------------------------------------------------------------------------
    |
    | Define which events are allowed to be tracked
    | Set to null to allow all events
    |
    */
    'allowed_events' => null,

    /*
   |--------------------------------------------------------------------------
   | Footprinting uniqueness
   |--------------------------------------------------------------------------
   |
   | If this setting is disabled, then a semi-unique footprint will be generated
   | for the request. The purpose of this is to enable tracking across,
   | browsers or where cookies might be blocked.
   |
   | Note that enabling this could cause request from different users using
   | the same ip to be matched.
   |
   */
    'uniqueness' => true,

    /*
    |--------------------------------------------------------------------------
    | Queue Settings
    |--------------------------------------------------------------------------
    |
    | Configure how visits and events are processed
    |
    */
    'queue' => [
        'batch_size' => env('TRACKERJACK_BATCH_SIZE', 100),
        'queue_name' => env('TRACKERJACK_QUEUE', 'default'),
        'retry_after' => env('TRACKERJACK_RETRY_AFTER', 60),
        'tries' => env('TRACKERJACK_TRIES', 3),
    ],
];
