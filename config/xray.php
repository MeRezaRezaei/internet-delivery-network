<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Xray Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the xray connections below you wish
    | to use as your default connection for all xray work.
    |
    */

    'default' => env('XRAY_CONNECTION', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Xray Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the xray connections setup for your application.
    |
    */

    'connections' => [

        'local' => [
            'host' => env('XRAY_HOST', '127.0.0.1'),
            'port' => env('XRAY_PORT', 10085),
            'secure' => env('XRAY_SECURE', false),
        ],

        'secondary' => [
            'host' => '127.0.0.1',
            'port' => 10087,
            'secure' => false,
        ],

    ],

];
