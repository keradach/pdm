<?php

use Illuminate\Support\Str;

return [

    'default' => env('DB_CONNECTION', 'oracle'),

    'connections' => [

        // ---------------------------------------------------------------
        // Oracle Database, provided by yajra/laravel-oci8
        // ---------------------------------------------------------------
        'oracle' => [
            'driver'         => 'oracle',
            'tns'            => env('DB_TNS', ''),
            'host'           => env('DB_HOST', 'oracle-db'),
            'port'           => env('DB_PORT', '1521'),
            'database'       => env('DB_DATABASE', 'FREEPDB1'),
            'service_name'   => env('DB_SERVICE_NAME', env('DB_DATABASE', 'FREEPDB1')),
            'username'       => env('DB_USERNAME', 'pdm_user'),
            'password'       => env('DB_PASSWORD', ''),
            'charset'        => env('DB_CHARSET', 'AL32UTF8'),
            'prefix'         => env('DB_PREFIX', ''),
            'prefix_schema'  => env('DB_SCHEMA_PREFIX', ''),
            'edition'        => env('DB_EDITION', 'ora$base'),
            'server_version' => env('DB_SERVER_VERSION', '11g'),
            'load_balance'   => env('DB_LOAD_BALANCE', 'yes'),
        ],

        // Kept available for local tooling / tests that don't need Oracle.
        'sqlite' => [
            'driver' => 'sqlite',
            'url' => env('DB_URL'),
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],

    ],

    'migrations' => [
        'table' => 'migrations',
        'update_date_on_publish' => true,
    ],

    'redis' => [
        'client' => env('REDIS_CLIENT', 'phpredis'),
        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => Str::slug(env('APP_NAME', 'laravel'), '_') . '_database_',
        ],
        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],
    ],

];
