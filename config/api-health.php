<?php

return [
    /**
     * Fill this array with all the checkers you want to run.
     */
    'checkers' => [
        // \App\Checkers\SomeServiceChecker::class,
    ],

    /**
     * The name of the cache driver the store the state of the checkers.
     * Choose a persistant store, not 'array'.
     */
    'cache_driver' => 'file',

    'retries' => [
        /**
         * The number of allowed retries.
         */
        'allowed_retries' => 0,

        /**
         * Here you can specify the configuration of the retry job.
         */
        'job' => [
            'job' => \ProtoneMedia\ApiHealth\Jobs\RetryChecker::class,

            'connection' => null,

            'delay' => null,

            'queue' => null,
        ],
    ],

    'notifications' => [
        /**
         *  Number of minutes until send the failed notification again.
         */
        'resend_failed_notification_after_minutes' => 60,

        /**
         * Class name of the failed notification.
         */
        'default_failed_notification' => \ProtoneMedia\ApiHealth\Notifications\CheckerHasFailed::class,

        /**
         * Class name of the recovered notification.
         */
        'default_recovered_notification' => \ProtoneMedia\ApiHealth\Notifications\CheckerHasRecovered::class,

        /**
         * Deliver the notifications through these channels.
         */
        'via' => [
            // 'mail', 'slack',
        ],

        /**
         * Class name of the notifiable.
         */
        'notifiable' => \ProtoneMedia\ApiHealth\Notifications\Notifiable::class,

        /**
         * Notifiable mail settings.
         */
        'mail' => [
            'to' => 'your@example.com',
        ],

        /**
         * Notifiable Slack settings.
         */
        'slack' => [
            'webhook_url' => '',

            'channel' => null,

            'username' => null,

            'icon' => null,
        ],
    ],
];
