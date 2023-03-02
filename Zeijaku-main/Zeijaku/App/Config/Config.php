<?php

return [
    'cookie' => [
        'encrypt'       => true,
        'sign'          => true,
        'sign_method'   => 'hash'
    ],
    'session' => [
        'startup'               => true,
        'manager' => [
            'regen_odds'        => 0,
            'ttl'               => 30,
            'match_ip'          => false,
            'netmask'           => '255.255.0.0',
            'match_user_agent'  => false,
        ],
        'handler' => [
            'cookie'            => 'PHPSESSID',
            'file_path'         => 'storage/sessions'
        ]
    ],
    'encryption' => [
        'cipher'        => MCRYPT_RIJNDAEL_128,
        'mode'          => MCRYPT_MODE_CBC,
        'key'           => 'NblOWl9evH8DsfY3aSjyX8korDB4Tsol',
        'algo'          => 'sha256',
        'options' => [
            'iv_source' => MCRYPT_DEV_URANDOM
        ]
    ],
    'hashing' => [
        'hash_algo' => 'sha256',
        'hmac_algo' => 'sha256',
        'pass_algo' => PASSWORD_BCRYPT
    ],
    'database' => [
        'driver'    => 'mysql',
        'host'      => 'localhost',
        'port'      => null,
        'dbname'    => 'sampleapp',
        'username'  => 'root',
        'password'  => '',
        'options'   => [
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_CASE               => PDO::CASE_NATURAL,
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_ORACLE_NULLS       => PDO::NULL_NATURAL,
            PDO::ATTR_STRINGIFY_FETCHES  => false,
            PDO::ATTR_EMULATE_PREPARES   => false
        ]
    ]
];