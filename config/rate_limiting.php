<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Allowed Requests
    |--------------------------------------------------------------------------
    |
    | This value determines how many requests to the fetch endpoint will be
    | accepted within a 10 minutes window before responding with a 429 status.
    | Depending on the "global" option below, this is applied per IP address
    | or shared across all clients.
    |
    */

    'requests' => (int) env('RATE_LIMITING_REQUESTS', 100),

    /*
    |--------------------------------------------------------------------------
    | Global Limit
    |--------------------------------------------------------------------------
    |
    | When enabled, the limit above is shared by all clients instead of being
    | applied to each IP address separately. This caps the total traffic that
    | can be relayed through this server, at the expense of availability: any
    | single client can exhaust the quota for everyone else. It is useful when
    | the proxy is not critical for the apps using it, and being unavailable
    | is preferable to being abused.
    |
    */

    'global' => (bool) env('RATE_LIMITING_GLOBAL', true),

];
