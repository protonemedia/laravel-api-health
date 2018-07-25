<?php

return [
    'checkers' => [
        // \App\Checkers\SomeServiceChecker::class,
    ],

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
