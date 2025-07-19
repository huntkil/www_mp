<?php

/**
 * 테스트 환경 설정
 */

return [
    'database' => [
        'driver' => 'sqlite',
        'database' => __DIR__ . '/../../tests/database/test.sqlite',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'prefix' => '',
    ],
    'app' => [
        'name' => 'MP Learning Platform',
        'env' => 'test',
        'debug' => true,
        'url' => 'http://localhost',
        'timezone' => 'Asia/Seoul',
        'locale' => 'ko',
    ],
    'logging' => [
        'default' => 'file',
        'channels' => [
            'file' => [
                'driver' => 'file',
                'path' => __DIR__ . '/../../system/logs/test.log',
                'level' => 'debug',
            ],
        ],
    ],
    'cache' => [
        'default' => 'file',
        'stores' => [
            'file' => [
                'driver' => 'file',
                'path' => __DIR__ . '/../../system/cache/test',
            ],
        ],
    ],
]; 