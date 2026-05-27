<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | AWS IP Ranges Source URL
    |--------------------------------------------------------------------------
    |
    | The URL of the official AWS IP ranges document. Override only if you
    | mirror this file internally or want to point at a fixture in tests.
    |
    */

    'url' => env('AWS_IP_RANGE_URL', 'https://ip-ranges.amazonaws.com/ip-ranges.json'),

    /*
    |--------------------------------------------------------------------------
    | Cache Key
    |--------------------------------------------------------------------------
    |
    | The cache store key used to memoize the parsed AWS IP range list.
    |
    */

    'cache_key' => env('AWS_IP_RANGE_CACHE_KEY', 'arubacao_aws-ip-ranges'),

    /*
    |--------------------------------------------------------------------------
    | Cache TTL (seconds)
    |--------------------------------------------------------------------------
    |
    | How long the parsed AWS IP range list is cached before being refetched.
    | Defaults to 24 hours.
    |
    */

    'cache_ttl' => (int) env('AWS_IP_RANGE_CACHE_TTL', 86400),

];
