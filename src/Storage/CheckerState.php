<?php

namespace Pbmedia\ApiHealth\Storage;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Cache;
use Pbmedia\ApiHealth\Checkers\Checker;
use Pbmedia\ApiHealth\Checkers\CheckerAllowsForRetries;
use Pbmedia\ApiHealth\Checkers\CheckerHasFailed;
use Pbmedia\ApiHealth\Events\CheckerHasFailed as CheckerHasFailedEvent;
use Pbmedia\ApiHealth\Events\CheckerHasRecovered;
use Pbmedia\ApiHealth\Events\CheckerIsStillFailing;

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
        return 'laravel-api-checker.' . $this->checker->id();
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
     * Determinates wether the checker is allowed to do another retry.
     *
     * @return bool
     */
    public function retryIsAllowed(): bool
    {
        if (!$this->checker instanceof CheckerAllowsForRetries) {
            return false;
        }

        if ($this->exists() && $this->isFailing()) {
            return false;
        }

        if (!$allowedRetries = $this->checker->allowedRetries()) {
            return false;
        }

        if (!$this->exists()) {
            return true;
        }

        $retries = $this->data()['retried_at'];

        return $allowedRetries > count($retries);
    }

    /**
     * Set the state to failed with the given exception message.
     *
     * @param \Pbmedia\ApiHealth\Checkers\CheckerHasFailed $exception
     */
    public function setToFailed(CheckerHasFailed $exception)
    {
        $this->store($failedData = [
            'exception_message'  => $exception->getMessage(),
            'passed_at'          => null,
            'failed_at'          => [now()->getTimestamp()],
            'notifications_sent' => [],
        ]);

        event(new CheckerHasFailedEvent($this->checker, $exception, $failedData));
    }

    /**
     * Adds the current timestamp to the failed state.
     *
     * @param \Pbmedia\ApiHealth\Checkers\CheckerHasFailed $exception
     */
    public function addFailedTimestamp(CheckerHasFailed $exception)
    {
        $failedData = $this->data();

        $failedData['failed_at'][] = now()->getTimestamp();

        $this->store($failedData);

        event(new CheckerIsStillFailing($this->checker, $exception, $failedData));
    }

    /**
     * Adds the current timestamp to the retry array.
     *
     * @return null
     */
    public function addRetryTimestamp()
    {
        $data = $this->data();

        $data['retried_at'][] = now()->getTimestamp();

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
        $failedData = ($this->exists() && $this->isFailing()) ? $this->data() : null;

        $this->store([
            'exception_message'  => null,
            'passed_at'          => now()->getTimestamp(),
            'failed_at'          => null,
            'notifications_sent' => [],
            'retried_at'         => [],
        ]);

        $failedData ? event(new CheckerHasRecovered($this->checker, $failedData)) : null;
    }
}
