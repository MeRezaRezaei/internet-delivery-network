<?php

return [
    'default' => 'mysql',

    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'marzban-arvan'),
            'username' => env('DB_USERNAME', 'marzban'),
            'password' => env('DB_PASSWORD', 'Marzban9011438678'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],
        'marzban' => [
            'driver' => 'mysql',
            'host' => env('MARZBAN_DB_HOST', '127.0.0.1'),
            'port' => env('MARZBAN_DB_PORT', '3306'),
            'database' => env('MARZBAN_DATABASE_NAME', 'marzban-arvan'),
            'username' => env('MARZBAN_DB_USERNAME', 'marzban'),
            'password' => env('MARZBAN_DB_PASSWORD', 'Marzban9011438678'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],
    ],

    'migrations' => [
        'table' => 'migrations',
        'update_date_on_publish' => true,
    ],

    'redis' => [
        'client' => 'phpredis',
        'default' => [
            'host' => '127.0.0.1',
            'password' => null,
            'port' => '6379',
            'database' => '0',
        ],
    ],
];
