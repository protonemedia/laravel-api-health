<?php

return [
    'checkers' => [
        // \App\Checkers\SomeServiceChecker::class,
    ],

    'cache_driver' => 'file',

    'storage_path' => storage_path('laravel-api-checker'),

    'notifications' => [
        'default_failed_notification' => \Pbmedia\ApiHealth\Notifications\CheckerHasFailed::class,

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
