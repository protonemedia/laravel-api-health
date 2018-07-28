<?php

namespace Pbmedia\ApiHealth\Storage;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Cache;
use Pbmedia\ApiHealth\Checkers\Checker;

class CheckerState
{
    /**
     * The checker.
     *
     * @var \Pbmedia\ApiHealth\Checkers\Checker
     */
    private $checker;

    /**
     * The cache driver instance.
     *
     * @var \Illuminate\Contracts\Cache\Repository
     */
    private $cache;

    public function __construct(Checker $checker)
    {
        $this->checker = $checker;

        $this->cache = Cache::driver(config('api-health.cache_driver'));
    }

    /**
     * Shortcut for creating an instance with the given checker class.
     *
     * @param  string $checkerClass
     * @return \Pbmedia\ApiHealth\Storage\CheckerState
     */
    public static function make(string $checkerClass)
    {
        return new static($checkerClass::create());
    }

    /**
     * Returns the cache key for this checker.
     *
     * @return string
     */
    private function key(): string
    {
        return 'laravel-api-checker.' . md5(get_class($this->checker));
    }

    /**
     * Returns a boolean wether any data has been stored.
     *
     * @return bool
     */
    public function exists(): bool
    {
        return $this->cache->has($this->key());
    }

    /**
     * Stores the given data into the cache repository.
     *
     * @param  array  $data
     * @return null
     */
    private function store(array $data)
    {
        $this->cache->forever($this->key(), $data);
    }

    /**
     * Returns all stored data.
     *
     * @return array
     */
    public function data(): array
    {
        return $this->cache->get($this->key());
    }

    /**
     * Returns a boolean wether the state is set to failed.
     *
     * @return bool
     */
    public function isFailing(): bool
    {
        return $this->data()['failed_at'] ? true : false;
    }

    /**
     * Returns a boolean wether the state is set to passes.
     *
     * @return bool
     */
    public function isPassing(): bool
    {
        return $this->data()['passed_at'] ? true : false;
    }

    /**
     * Determinates if a failed notification should be sent.
     *
     * @return bool
     */
    public function shouldSentFailedNotification(): bool
    {
        $successiveFailuresRequired = $this->checker->onlySendFailedNotificationAfterSuccessiveFailures();

        $failures = collect($this->data()['failed_at']);

        if ($failures->count() < $successiveFailuresRequired) {
            return false;
        }

        $sentNotifications = collect($this->data()['notifications_sent']);

        if ($sentNotifications->isEmpty()) {
            return true;
        }

        if (!$resendAfterMinutes = $this->checker->resendFailedNotificationAfterMinutes()) {
            return false;
        }

        $diffInSeconds = now()->getTimestamp() - $sentNotifications->last()['sent_at'];

        return $diffInSeconds >= ($resendAfterMinutes * 60);
    }

    /**
     * Set the state to failed with the given exception message.
     *
     * @param string $exceptionMessage
     */
    public function setToFailed(string $exceptionMessage)
    {
        $this->store([
            'exception_message'  => $exceptionMessage,
            'passed_at'          => null,
            'failed_at'          => [now()->getTimestamp()],
            'notifications_sent' => [],
        ]);
    }

    /**
     * Adds the current timestamp to the failed state.
     *
     * @return null
     */
    public function addFailedTimestamp()
    {
        $data = $this->data();

        $data['failed_at'][] = now()->getTimestamp();

        $this->store($data);
    }

    /**
     * Adds the current timestamp to the array of sent notifications.
     *
     * @param  \Illuminate\Notifications\Notification $notification
     * @return null
     */
    public function markSentFailedNotification(Notification $notification)
    {
        $data = $this->data();

        $data['notifications_sent'][] = [
            'notification_type' => get_class($notification),
            'sent_at'           => now()->getTimestamp(),
        ];

        $this->store($data);
    }

    /**
     * Set the state to passes.
     * @return null
     */
    public function setToPassing()
    {
        $this->store([
            'exception_message'  => null,
            'passed_at'          => now()->getTimestamp(),
            'failed_at'          => null,
            'notifications_sent' => [],
        ]);
    }
}
