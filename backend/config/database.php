<?php
return [
    'database' => [
        'type' => 'mysql',
        'hostname' => '127.0.0.1',
        'database' => 'think',
        'username' => 'think',
        'password' => '123456',
        'hostport' => '3306',
        'charset' => 'utf8mb4',
        'prefix' => '',
        'timeout' => 300,
    ],
    'upload' => [
        'path' => 'uploads',
        'max_size' => 10 * 1024 * 1024,
        'allowed_ext' => ['jpg', 'jpeg', 'png', 'gif'],
    ],
    'jwt' => [
        'secret' => 'escape_room_script_platform_secret_key_2024',
        'expire' => 86400 * 7,
    ],
];
