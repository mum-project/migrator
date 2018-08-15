<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    |
    | All database work in Laravel is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
    */

    'connections' => [
        'mysql_vimbadmin' => [
            'driver' => 'mysql',
            'host' => env('VIMBADMIN_DB_HOST', '127.0.0.1'),
            'port' => env('VIMBADMIN_DB_PORT', '3306'),
            'database' => env('VIMBADMIN_DB_DATABASE', 'vimbadmin'),
            'username' => env('VIMBADMIN_DB_USERNAME', 'vimbadmin'),
            'password' => env('VIMBADMIN_DB_PASSWORD', ''),
            'unix_socket' => env('VIMBADMIN_DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],
        'mysql_mum' => [
            'driver' => 'mysql',
            'host' => env('MUM_DB_HOST', '127.0.0.1'),
            'port' => env('MUM_DB_PORT', '3306'),
            'database' => env('MUM_DB_DATABASE', 'mum'),
            'username' => env('MUM_DB_USERNAME', 'mum'),
            'password' => env('MUM_DB_PASSWORD', ''),
            'unix_socket' => env('MUM_DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],
    ],

];
