<?php

return [
    'checkers' => [
        [
            'checker'             => '', // \App\Checkers\SomeServiceChecker::class,

            'failed_notification' => null,
        ],
    ],

    'cache_driver' => 'file',

    'storage_path' => storage_path('laravel-api-checker'),

    'notifications' => [
        'via' => [
            // 'mail', 'slack',
        ],

        'notifiable' => \Pbmedia\ApiHealth\Notifications\Notifiable::class,

        'mail' => [
            'to' => 'your@example.com',
        ],

        'slack' => [
            'webhook_url' => '',

            'channel' => null,

            'username' => null,

            'icon' => null,
        ],
    ],
];
