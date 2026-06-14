<?php

return [
    'default' => 'mysql',

    'connections' => [
        'mysql' => [
            'driver' => env('DB_DRIVER', 'mysql'),
            'host' => env('DB_HOST', 'mysql'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'idn_db'),
            'username' => env('DB_USERNAME', 'idn_user'),
            'password' => env('DB_PASSWORD', 'marzban_password'),
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
            'host' => env('MARZBAN_DB_HOST', 'marzban-mysql'),
            'port' => env('MARZBAN_DB_PORT', '3306'),
            'database' => env('MARZBAN_DATABASE_NAME', 'marzban'),
            'username' => env('MARZBAN_DB_USERNAME', 'marzban_user'),
            'password' => env('MARZBAN_DB_PASSWORD', 'marzban_password'),
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
        'client' => env('REDIS_CLIENT', 'phpredis'),
        'default' => [
            'host' => env('REDIS_HOST', 'mysql'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],
        'cache' => [
            'host' => env('REDIS_HOST', 'mysql'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
        ],
    ],
];
