<?php

return [
    'default' => 'mysql',

    'connections' => [
        'mysql' => [
            'driver' => env('DB_DRIVER', 'mysql'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'marzban'),
            'username' => env('DB_USERNAME', 'marzban'),
            'password' => env('DB_PASSWORD', 'Marzban9011438678'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => env('DB_PREFIX', ''),
            'strict' => true,
            'engine' => null,
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
            'options' => [
                \PDO::ATTR_PERSISTENT => true,
            ],
        ],
        'marzban' => [
            'driver' => env('MARZBAN_DB_DRIVER', 'mysql'),
            'host' => env('MARZBAN_DB_HOST', '127.0.0.1'),
            'port' => env('MARZBAN_DB_PORT', '3306'),
            'database' => env('MARZBAN_DATABASE_NAME', 'marzban-arvan'),
            'username' => env('MARZBAN_DB_USERNAME', 'marzban'),
            'password' => env('MARZBAN_DB_PASSWORD', 'Marzban9011438678'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => env('DB_PREFIX', ''),
            'strict' => true,
            'engine' => null,
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
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
