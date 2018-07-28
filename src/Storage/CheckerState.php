<?php

namespace Pbmedia\ApiHealth\Storage;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Cache;
use Pbmedia\ApiHealth\Checkers\Checker;

class CheckerState
{
    private $checker;
    private $cache;

    public function __construct(Checker $checker)
    {
        $this->checker = $checker;

        $this->cache = Cache::driver(config('api-health.cache_driver'));
    }

    public static function make(string $checkerClass)
    {
        return new static($checkerClass::create());
    }

    private function key(): string
    {
        return 'laravel-api-checker.' . md5(get_class($this->checker));
    }

    public function data(): array
    {
        return $this->cache->get($this->key());
    }

    public function shouldSentFailedNotification(): bool
    {
        $sentNotifications = collect($this->data()['notifications_sent']);

        if ($sentNotifications->isEmpty()) {
            return true;
        }

        $resendAfterMinutes = $this->checker->resendFailedNotificationAfterMinutes();

        if (!$resendAfterMinutes) {
            return false;
        }

        $diffInSeconds = now()->getTimestamp() - $sentNotifications->last()['sent_at'];

        return $diffInSeconds >= ($resendAfterMinutes * 60);
    }

    public function setToFailed(string $exceptionMessage)
    {
        $this->cache->forever($this->key(), [
            'exception_message'  => $exceptionMessage,
            'passed_at'          => null,
            'failed_at'          => now()->getTimestamp(),
            'notifications_sent' => [],
        ]);
    }

    public function setToPassing()
    {
        $this->cache->forever($this->key(), [
            'exception_message'  => null,
            'passed_at'          => now()->getTimestamp(),
            'failed_at'          => null,
            'notifications_sent' => [],
        ]);
    }

    public function markSentFailedNotification(Notification $notification)
    {
        $data = $this->data();

        $data['notifications_sent'][] = [
            'notification_type' => get_class($notification),
            'sent_at'           => now()->getTimestamp(),
        ];

        $this->cache->forever($this->key(), $data);
    }

    public function exists(): bool
    {
        return $this->cache->has($this->key());
    }

    public function isFailing(): bool
    {
        return $this->data()['failed_at'] ? true : false;
    }

    public function isPassing(): bool
    {
        return $this->data()['passed_at'] ? true : false;
    }
}
